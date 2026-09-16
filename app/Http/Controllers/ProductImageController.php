<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
class ProductImageController extends Controller
{

    public function __construct(protected \App\Services\CloudinaryService $cloudinary) {}

    public function store(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'image' => 'required|image|max:5120',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'is_primary' => 'boolean',
        ]);

        $publicId = $this->cloudinary->upload($validated['image']->getRealPath());

        $product->images()->create([
            'product_variant_id' => $validated['product_variant_id'] ?? null,
            'cloudinary_public_id' => $publicId,
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return back()->with('success', 'Image uploaded.');
    }


    public function destroy(\App\Models\ProductImage $image)
    {
        $this->authorize('update', $image->product);

        $image->delete(); // ProductImageObserver::deleting() fires here → Cloudinary::destroy()

        return back()->with('success', 'Image removed.');
    }
}
