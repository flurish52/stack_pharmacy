<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // Statuses the customer gets an email for.
    public const NOTIFY_ON = ['processing', 'ready_for_pickup', 'out_for_delivery', 'cancelled'];

    public function __construct(public Order $order, public string $status) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->status) {
            'processing' => 'We are preparing your order',
            'ready_for_pickup' => 'Your order is ready for pickup',
            'out_for_delivery' => 'Your order is on its way',
            'cancelled' => 'Your order has been cancelled',
            default => 'Update on your order',
        };

        return new Envelope(subject: "{$subject} (#{$this->order->id})");
    }

    public function content(): Content
    {
        $this->order->loadMissing('pickupPoint');

        return new Content(markdown: 'emails.orders.order-status');
    }
}
