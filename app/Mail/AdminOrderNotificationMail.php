<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function build(): static
    {
        return $this->subject("New Order — #{$this->order->id}")
            ->markdown('emails.orders.admin-notification', [
                'order' => $this->order->load('items.variant.product'),
            ]);
    }
}
