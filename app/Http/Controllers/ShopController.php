<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category'  => ['nullable', 'string', 'exists:categories,slug'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'sort'      => ['nullable', 'string', 'in:popular,price_asc,price_desc,newest'],
        ]);

        $sort = $validated['sort'] ?? 'popular';

        $products = Product::query()
            ->where('status', 'active')
            ->with([
                'variants',
                'images',
                'categories:id,name,slug',
            ])
            ->when(
                !empty($validated['category']),
                fn ($query) =>
                $query->whereHas(
                    'categories',
                    fn ($q) => $q->where('slug', $validated['category'])
                )
            )
            ->when(
                isset($validated['min_price']),
                fn ($query) =>
                $query->whereHas(
                    'variants',
                    fn ($q) => $q->where('price', '>=', $validated['min_price'])
                )
            )
            ->when(
                isset($validated['max_price']),
                fn ($query) =>
                $query->whereHas(
                    'variants',
                    fn ($q) => $q->where('price', '<=', $validated['max_price'])
                )
            )
            ->when($sort === 'newest', fn ($query) => $query->latest())
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('starting_price', 'asc'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderBy('starting_price', 'desc'))
            ->when($sort === 'popular', fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->transform(function ($product) {
            $variants = $product->variants ?? collect();

            $availableVariant = $variants
                ->filter(fn ($variant) => (int) ($variant->stock_quantity ?? 0) > 0)
                ->sortBy(fn ($variant) => (float) ($variant->price ?? 0))
                ->first();

            $categories = $product->categories ?? collect();

            return [
                'id' => $product->id,
                'name' => $product->name ?? 'Unnamed product',
                'slug' => $product->slug ?? null,
                'starting_price' => $product->starting_price ?? null,
                'image_url' => $product->primary_image_url ?? null,

                'in_stock' => $availableVariant !== null,
                'variant_id' => $availableVariant?->id,
                'variant_count' => $variants->count(),

                'categories' => $categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name ?? 'Uncategorized',
                    'slug' => $category->slug ?? null,
                ])->values()->all(),
            ];
        });

        return Inertia::render('Shop/Index', [
            'products' => $products,
            'categories' => Category::select('id', 'name', 'slug')->orderBy('name')->get(),
            'filters' => [
                'category' => $validated['category'] ?? null,
                'min_price' => $validated['min_price'] ?? null,
                'max_price' => $validated['max_price'] ?? null,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Product $product)
    {
        // A draft/archived product should behave as if it doesn't exist publicly
        abort_unless($product->status === 'active', 404);

        $product->load([
            'variants' => fn ($q) => $q->orderBy('price'),
            'images',
            'categories:id,name,slug',
        ]);

        $isOutOfStock = $product->variants->where('stock_quantity', '>', 0)->isEmpty();

        $relatedProducts = Product::query()
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->whereHas('categories', fn ($q) =>
            $q->whereIn('categories.id', $product->categories->pluck('id'))
            )
            ->whereHas('variants', fn ($q) => $q->where('stock_quantity', '>', 0))
            ->with(['variants', 'images'])
            ->limit(4)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'starting_price' => $p->starting_price,
                'primary_image_url' => $p->primary_image_url,
            ]);

        return Inertia::render('Shop/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'is_out_of_stock' => $isOutOfStock,
                'variants' => $product->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'variant_name' => $v->variant_name,
                    'sku' => $v->sku,
                    'price' => $v->price,
                    'stock_quantity' => $v->stock_quantity,
                ]),
                'images' => $product->images->map(fn ($i) => [
                    'id' => $i->id,
                    'url' => $i->url,
                    'is_primary' => $i->is_primary,
                    'product_variant_id' => $i->product_variant_id,
                ]),
                'categories' => $product->categories->map(fn ($c) => [
                    'id' => $c->id, 'name' => $c->name, 'slug' => $c->slug,
                ]),
            ],
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
