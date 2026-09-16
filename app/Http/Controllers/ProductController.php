<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['categories', 'variants', 'images'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Products/Index', ['products' => $products]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Admin/Products/Create', [
            'categories' => \App\Models\Category::all(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->safe()->except('category_ids'));
        $product->categories()->sync($request->validated('category_ids'));

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product->load(['categories', 'variants', 'images']),
            'categories' => \App\Models\Category::all(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->safe()->except('category_ids'));
        $product->categories()->sync($request->validated('category_ids'));

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete(); // soft delete — safe, order history intact

        return redirect()->route('admin.products.index')->with('success', 'Product removed.');
    }
}
