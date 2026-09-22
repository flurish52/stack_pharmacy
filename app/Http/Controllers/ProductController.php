<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'category', 'trashed']);

        // variants + images must be eager-loaded: starting_price and
        // primary_image_url (in $appends) read them for every row.
        $products = Product::query()
            ->with(['categories:id,name', 'variants', 'images'])
            ->when(($filters['trashed'] ?? null) === '1', fn ($q) => $q->onlyTrashed())
            ->when($filters['search'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', "%{$term}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->whereHas(
                'categories', fn ($c) => $c->where('categories.id', $category)
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'category' => $filters['category'] ?? '',
                'trashed' => $filters['trashed'] ?? '',
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'statuses' => ['active', 'draft', 'archived'],
            // Keep this in step with whatever your dashboard's low-stock count uses.
            'lowStockThreshold' => (int) config('pharmacy.low_stock_threshold', 10),
            'canForceDelete' => $request->user()->hasRole('super_admin'),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $product = DB::transaction(function () use ($request) {
            $product = Product::create($request->productData());

            $this->syncCategories($product, $request->validated('category_ids'), $request->validated('primary_category_id'));
            $this->syncVariants($product, $request->validated('variants'));

            return $product;
        });

        // Images need a saved product, so go straight to the edit page.
        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product created. You can add images below.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $product->load(['categories', 'variants', 'images']);

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'primaryCategoryId' => $product->primaryCategory()?->id,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        DB::transaction(function () use ($request, $product) {
            $product->update($request->productData());

            $this->syncCategories($product, $request->validated('category_ids'), $request->validated('primary_category_id'));
            $this->syncVariants($product, $request->validated('variants'));
        });

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete(); // soft delete: order history stays intact

        return back()->with('success', 'Product removed. You can restore it from the Trashed tab.');
    }

    public function restore(int $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $product);

        $product->restore();

        return back()->with('success', 'Product restored.');
    }

    public function forceDelete(int $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $product);

        $variantIds = $product->variants()->withTrashed()->pluck('id');

        if (OrderItem::whereIn('product_variant_id', $variantIds)->exists()) {
            return back()->with('error', 'This product appears in past orders, so it cannot be permanently deleted.');
        }

        DB::transaction(function () use ($product) {
            // Model-level deletes so the image observer also clears Cloudinary.
            $product->images()->withTrashed()->get()->each->forceDelete();
            $product->variants()->withTrashed()->get()->each->forceDelete();
            $product->forceDelete();
        });

        return back()->with('success', 'Product permanently deleted.');
    }

    private function syncCategories(Product $product, array $categoryIds, int $primaryId): void
    {
        $product->categories()->sync(
            collect($categoryIds)->mapWithKeys(fn ($id) => [
                $id => ['is_primary' => (int) $id === $primaryId],
            ])->all()
        );
    }

    /**
     * Create, update and remove variants to match the submitted list.
     */
    private function syncVariants(Product $product, array $rows): void
    {
        $keep = [];

        foreach ($rows as $row) {
            $data = Arr::only($row, ['variant_name', 'sku', 'price', 'stock_quantity']);

            // Don't rely on the frontend to always send one — a blank/missing
            // SKU gets a real one here, so the row is never saved without one.
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSku($product, $row['variant_name'] ?? null);
            }

            $variant = ! empty($row['id'])
                ? tap($product->variants()->findOrFail($row['id']))->update($data)
                : $product->variants()->create($data);

            $keep[] = $variant->id;
        }

        // Variants dropped in the form are soft-deleted, so past orders keep them.
        // The SKU is renamed first so it can be reused later (it is UNIQUE in the DB).
        $product->variants()->whereNotIn('id', $keep)->get()->each(function ($variant) {
            $variant->update(['sku' => $variant->sku.'-removed-'.$variant->id]);
            $variant->delete();
        });
    }

    /**
     * STK-XXXXXXXX, checked against SKUs including soft-deleted variants —
     * the UNIQUE constraint doesn't care that a row is trashed, so a
     * collision there would still fail the insert.
     */
    private function generateSku(Product $product, ?string $variantName = null): string
    {
        do {
            $sku = 'STK-'.strtoupper(Str::random(8));
        } while (ProductVariant::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }
}
