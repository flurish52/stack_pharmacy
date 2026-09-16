<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        return Inertia::render('Cart/Index', $this->hydrateCart());
    }

    public function store(Request $request)
    {
        $cart = session('cart', []);

        abort_if(empty($cart), 422, 'Cart is empty.');

        $variants = ProductVariant::whereIn('id', array_keys($cart))->get()->keyBy('id');

        foreach ($cart as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            abort_if(! $variant || $quantity > $variant->stock_quantity, 422, 'One or more items are no longer available in the requested quantity.');
        }

        $order = DB::transaction(function () use ($request, $cart, $variants) {
            $total = collect($cart)->sum(fn ($qty, $variantId) => $variants[$variantId]->price * $qty);

            $order = Order::create([
                'user_id' => $request->user()?->id,
                'guest_email' => $request->user() ? null : $request->input('email'),
                'guest_phone' => $request->user() ? null : $request->input('phone'),
                'fulfillment_type' => $request->input('fulfillment_type'),
                'pickup_point_id' => $request->input('pickup_point_id'),
                'delivery_address' => $request->input('delivery_address'),
                'status' => 'pending',
                'paystack_reference' => 'ORD-' . strtoupper(Str::random(12)),
                'total_amount' => $total,
            ]);

            foreach ($cart as $variantId => $quantity) {
                $order->items()->create([
                    'product_variant_id' => $variantId,
                    'quantity' => $quantity,
                    'price' => $variants[$variantId]->price,
                ]);
            }

            $order->statusHistory()->create(['status' => 'pending', 'changed_by' => $request->user()?->id]);

            return $order;
        });

        $response = $this->paystack->initializeTransaction(
            email: $request->user()?->email ?? $request->input('email'),
            amountInKobo: (int) ($order->total_amount * 100),
            reference: $order->paystack_reference,
            callbackUrl: route('checkout.callback'),
        );

        abort_unless($response['status'] ?? false, 500, 'Could not initialize payment.');

        session()->forget('cart'); // clear only after successful order + payment init

        return Inertia::location($response['data']['authorization_url']);
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate(['quantity' => 'required|integer|min:1']);

        abort_if($validated['quantity'] > $variant->stock_quantity, 422, 'Not enough stock available.');

        $cart = session('cart', []);
        $cart[$variant->id] = $validated['quantity'];
        session(['cart' => $cart]);

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

    protected function hydrateCart(): array
    {
        $cart = session('cart', []);

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
