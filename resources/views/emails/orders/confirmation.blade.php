@component('mail::message')
    # Thanks for your order!

    Hi {{ $order->user->name ?? 'there' }}, your order **#{{ $order->id }}** has been confirmed.

    @component('mail::table')
        | Item | Qty | Price |
        |:-----|:---:|------:|
        @foreach ($order->items as $item)
            | {{ $item->variant->product->name }} ({{ $item->variant->variant_name }}) | {{ $item->quantity }} | ₦{{ number_format($item->price * $item->quantity, 2) }} |
        @endforeach
    @endcomponent

    **Total: ₦{{ number_format($order->total_amount, 2) }}**

    @if ($order->fulfillment_type === 'pickup')
        You've chosen pickup at **{{ $order->pickupPoint->name }}**. Delivery fee does not apply — pay only for your items.
    @else
        Your order will be delivered to: {{ $order->delivery_address }}. Delivery fee is collected on arrival.
    @endif

    @component('mail::button', ['url' => route('account.orders.show', $order)])
        View Order
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
