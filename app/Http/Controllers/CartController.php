<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Support\CartSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(protected CartSession $cart) {}

    public function index()
    {
        return Inertia::render('Cart/Index', $this->cart->items());
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity'   => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $variant = ProductVariant::with('product')->findOrFail($validated['variant_id']);

        $requested = ($this->cart->all()[$variant->id]['quantity'] ?? 0) + $validated['quantity'];

        if ($requested > $variant->stock_quantity) {
            return back()->with('error', "Only {$variant->stock_quantity} left in stock.");
        }

        $this->cart->add($variant->id, $validated['quantity'], [
            'name'  => $variant->product->name,
            'image' => $variant->image_url ?? $variant->product->image_url,
            'price' => $variant->price,
        ]);

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        if ($validated['quantity'] > $variant->stock_quantity) {
            return back()->with('error', 'Not enough stock available.');
        }

        $this->cart->set($variant->id, $validated['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(ProductVariant $variant)
    {
        $cart = session('cart', []);
        unset($cart[$variant->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Removed from cart.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back();
    }
}
