<?php
@component('mail::message')
    # New order received

    Order **#{{ $order->id }}** — ₦{{ number_format($order->total_amount, 2) }} — {{ $order->fulfillment_type === 'pickup' ? 'Pickup' : 'Delivery' }}

    Customer: {{ $order->user->name ?? $order->guest_email }}

    @component('mail::button', ['url' => route('admin.orders.show', $order)])
        View in Admin
    @endcomponent
@endcomponent
