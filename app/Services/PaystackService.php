<?php

namespace App\Services;

use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PaystackService
{
    protected string $baseUrl;
    protected string $secretKey;

    public function __construct(
        protected PushNotificationService $push,
    ) {
        $this->baseUrl = config('services.paystack.payment_url');
        $this->secretKey = config('services.paystack.secret_key');
    }


    public function initializeTransaction(string $email, int $amountInKobo, string $reference, string $callbackUrl): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $email,
                'amount' => $amountInKobo,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
            ]);

        return $response->json();
    }

    public function verifyTransaction(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$reference}");

        return $response->json();
    }


    public function markSuccessful(Payment $payment, array $verificationData): void
    {
        if ($payment->status === 'success') {
            return;
        }

        $alerts = [];

        DB::transaction(function () use ($payment, $verificationData, &$alerts) {
            $payment->update([
                'status' => 'success',
                'channel' => $verificationData['channel'] ?? null,
                'gateway_response' => $verificationData,
                'paid_at' => now(),
            ]);

            $order = $payment->order;
            $order->update(['status' => 'paid']);
            $order->statusHistory()->create(['status' => 'paid']);

            $threshold = (int) config('pharmacy.low_stock_threshold', 10);

            foreach ($order->items as $item) {
                $variant = $item->variant()->withTrashed()->lockForUpdate()->with('product')->first();

                if (! $variant) {
                    continue;
                }

                $before = (int) $variant->stock_quantity;
                $after = max(0, $before - $item->quantity); // never below zero

                $variant->update(['stock_quantity' => $after]);

                $name = ($variant->product?->name ?? 'A product').' ('.$variant->variant_name.')';
                $url = '/admin/products/'.$variant->product_id.'/edit';

                // Alert once, at the moment stock crosses the line, not on every later sale.
                if ($after === 0 && $before > 0) {
                    $alerts[] = ['title' => 'Out of stock', 'body' => "{$name} is now out of stock.", 'url' => $url];
                } elseif ($after <= $threshold && $before > $threshold) {
                    $alerts[] = ['title' => 'Low stock', 'body' => "{$name} is down to {$after} units.", 'url' => $url];
                }
            }
        });

        // Sent after the transaction commits; a push failure can never undo a payment.
        foreach ($alerts as $alert) {
            try {
                $this->push->notifyAdmins(...$alert);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $order = $payment->order()->with('user')->first();
        $recipientEmail = $order->user->email ?? $order->guest_email;
        Mail::to($recipientEmail)->queue(new OrderConfirmationMail($order));

        $adminEmails = User::role(['super_admin', 'owner'])->pluck('email');
        Mail::to($adminEmails)->queue(new AdminOrderNotificationMail($order));
    }
}
