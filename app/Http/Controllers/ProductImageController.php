<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductImageController extends Controller
{
    public function __construct(protected CloudinaryService $cloudinary) {}

    public function store(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'product_variant_id' => [
                'nullable',
                // the variant must belong to this product
                \Illuminate\Validation\Rule::exists('product_variants', 'id')->where('product_id', $product->id),
            ],
            'is_primary' => ['boolean'],
        ]);

        // The first image is always the primary one.
        $makePrimary = ! $product->images()->exists() || ($validated['is_primary'] ?? false);

        try {
            $publicId = $this->cloudinary->upload($validated['image']->getRealPath());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'The image could not be uploaded. Please try again.');
        }

        DB::transaction(function () use ($product, $validated, $publicId, $makePrimary) {
            if ($makePrimary) {
                $product->images()->update(['is_primary' => false]);
            }

            $product->images()->create([
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'cloudinary_public_id' => $publicId,
                'is_primary' => $makePrimary,
            ]);
        });

        return back()->with('success', 'Image uploaded.');
    }

    public function setPrimary(ProductImage $image)
    {
        $this->authorize('update', $image->product);

        DB::transaction(function () use ($image) {
            $image->product->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

        return back()->with('success', 'Primary image updated.');
    }

    public function destroy(ProductImage $image)
    {
        $product = $image->product;

        $this->authorize('update', $product);

        $wasPrimary = $image->is_primary;

        $image->delete(); // ProductImageObserver::deleting() fires here -> Cloudinary destroy

        // Never leave a product with images but no primary.
        if ($wasPrimary) {
            $product->images()->oldest('id')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Image removed.');
    }
}
