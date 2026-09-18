<?php

namespace App\Support;

use App\Models\ProductVariant;

/**
 * One place that knows the shape of session('cart').
 *
 * The array is [variant_id => quantity]. CartController and
 * HandleInertiaRequests both go through here so the cart badge and the cart
 * page can never disagree.
 */
class CartSession
{
    /** Cache per request — the middleware and the controller both ask. */
    protected ?array $summary = null;

    public function all(): array
    {
        return session('cart', []);
    }

    public function put(array $cart): void
    {
        session(['cart' => $cart]);
        $this->summary = null;
    }

    public function add(int $variantId, int $quantity = 1): void
    {
        $cart = $this->all();
        $cart[$variantId] = ($cart[$variantId] ?? 0) + $quantity;
        $this->put($cart);
    }

    public function set(int $variantId, int $quantity): void
    {
        $cart = $this->all();
        $cart[$variantId] = $quantity;
        $this->put($cart);
    }

    public function forget(int $variantId): void
    {
        $cart = $this->all();
        unset($cart[$variantId]);
        $this->put($cart);
    }

    public function clear(): void
    {
        session()->forget('cart');
        $this->summary = null;
    }

    /**
     * Small payload shared on every Inertia response: enough for the header
     * badge and for a product card to know it's already in the cart.
     *
     * [count, total, items => [variant_id => quantity]]
     */
    public function summary(): array
    {
        if ($this->summary !== null) {
            return $this->summary;
        }

        $cart = $this->all();

        if (empty($cart)) {
            return $this->summary = ['count' => 0, 'total' => 0, 'items' => (object) []];
        }

        $prices = ProductVariant::whereIn('id', array_keys($cart))->pluck('price', 'id');

        // Drop variants that no longer exist so the total stays honest.
        $cart = array_intersect_key($cart, $prices->toArray());
        $this->put($cart);

        $total = 0;
        foreach ($cart as $variantId => $quantity) {
            $total += $prices[$variantId] * $quantity;
        }

        return $this->summary = [
            'count' => array_sum($cart),
            'total' => $total,
            'items' => (object) $cart,
        ];
    }

    /** Full line items for the cart page. */
    public function items(): array
    {
        $cart = $this->all();

        if (empty($cart)) {
            return ['items' => [], 'total' => 0];
        }

        $variants = ProductVariant::with(['product', 'images'])
            ->whereIn('id', array_keys($cart))
            ->get();

        $items = $variants->map(fn ($variant) => [
            'variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_name' => $variant->variant_name,
            'price' => $variant->price,
            'quantity' => $cart[$variant->id],
            'stock_quantity' => $variant->stock_quantity,
            'subtotal' => $variant->price * $cart[$variant->id],
            'image_url' => $variant->images->first()?->url,
        ])->values();

        return [
            'items' => $items,
            'total' => $items->sum('subtotal'),
        ];
    }
}
