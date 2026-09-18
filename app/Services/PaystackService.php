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
        // Idempotency guard lives here too, not just in the callers —
        // this is the one place that's allowed to apply the side effects.
        if ($payment->status === 'success') {
            return;
        }

        DB::transaction(function () use ($payment, $verificationData) {
            $payment->update([
                'status' => 'success',
                'channel' => $verificationData['channel'] ?? null,
                'gateway_response' => $verificationData,
                'paid_at' => now(),
            ]);

            $order = $payment->order;
            $order->update(['status' => 'paid']);
            $order->statusHistory()->create(['status' => 'paid']);

            foreach ($order->items as $item) {
                $variant = $item->variant()->lockForUpdate()->first();
                $variant->decrement('stock_quantity', $item->quantity);

                if ($variant->stock_quantity <= config('app.low_stock_threshold')) {
                    $this->push->notifyAdmins(
                        title: 'Low Stock Alert',
                        body: "{$variant->product->name} ({$variant->variant_name}) is down to {$variant->stock_quantity} units.",
                    );
                }
            }
        });

        $order = $payment->order()->with('user')->first();
        $recipientEmail = $order->user->email ?? $order->guest_email;
        Mail::to($recipientEmail)->queue(new OrderConfirmationMail($order));

        $adminEmails = User::role(['super_admin', 'owner'])->pluck('email');
        Mail::to($adminEmails)->queue(new AdminOrderNotificationMail($order));
    }
}
