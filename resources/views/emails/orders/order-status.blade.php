[x-mail::message](x-mail::message)

# Hi {{ $order->full_name ?? 'there' }},

@if ($status === 'processing')
    We have started preparing your order **#{{ $order->id }}**.

@elseif ($status === 'ready_for_pickup')
    Your order **#{{ $order->id }}** is ready for pickup at:

    **{{ $order->pickupPoint?->name }}**

    {{ $order->pickupPoint?->address }}

@elseif ($status === 'out_for_delivery')
    Your order **#{{ $order->id }}** is on its way to:

    {{ $order->delivery_address }}

@elseif ($status === 'cancelled')
    Your order **#{{ $order->id }}** has been cancelled. If you paid online, our team will contact you about your refund.
@endif

@if ($order->user_id)
    <x-mail::button :url="route('account.orders.show', $order)">
        View order
    </x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
