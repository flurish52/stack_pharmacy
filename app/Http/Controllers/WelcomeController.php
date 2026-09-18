<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\Training;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'services' => Service::where('is_active', true)
                ->limit(3)
                ->get()
                ->map(fn ($service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'whatsapp_url' => $service->whatsappUrl(),
                ]),

            'categories' => Category::withCount('products')
                ->having('products_count', '>', 0)
                ->limit(6)
                ->get(['id', 'name', 'slug']),

            'featuredProducts' => Product::query()
                ->where('status', 'active')
                ->whereHas('variants', fn ($q) => $q->where('stock_quantity', '>', 0))
                ->with(['variants', 'images'])
                ->latest()
                ->limit(8)
                ->get()
                ->map(function ($product) {
                    // Already scoped to products with ≥1 in-stock variant by whereHas
                    // above, but re-derive it here so this map stays correct if that
                    // constraint is ever loosened.
                    $inStockVariants = $product->variants->where('stock_quantity', '>', 0);
                    $defaultVariant = $inStockVariants->sortBy('price')->first();

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'image_url' => $product->primary_image_url,
                        'price' => $product->starting_price,
                        'compare_at_price' => $product->compare_at_price, // null if you don't have this column
                        'variant_id' => $defaultVariant?->id,
                        'variant_count' => $product->variants->count(),
                        'in_stock' => $inStockVariants->isNotEmpty(),
                    ];
                }),

            'training' => Training::first(),
        ]);
    }
}
