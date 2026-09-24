<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $categories = Category::withCount('products')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create(Arr::only($request->validated(), ['name', 'slug']));

        return $category->syncImage($request)
            ? back()->with('success', 'Category created.')
            : back()->with('error', 'Category created, but the image upload failed. Try adding the image again.');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update(Arr::only($request->validated(), ['name', 'slug']));

        return $category->syncImage($request)
            ? back()->with('success', 'Category updated.')
            : back()->with('error', 'Category updated, but the image upload failed. Try again.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            return back()->with('error', 'This category still has products assigned to it. Reassign them first.');
        }

        $category->delete();

        return back()->with('success', 'Category removed.');
    }
}
