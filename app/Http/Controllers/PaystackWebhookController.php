<?php

namespace App\Http\Controllers;

use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Services\PaystackService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaystackWebhookController extends Controller
{


    public function __construct(
        protected PaystackService $paystack,
        protected PushNotificationService $push,
    ) {}

    public function handle(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $computedSignature = hash_hmac('sha512', $request->getContent(), config('services.paystack.secret_key'));

        if (! hash_equals($computedSignature, (string) $signature)) {
            Log::warning('Paystack webhook: invalid signature');
            abort(400);
        }

        $payload = $request->input();

        if ($payload['event'] !== 'charge.success') {
            return response()->json(['status' => 'ignored']);
        }

        $reference = $payload['data']['reference'];

        $verification = $this->paystack->verifyTransaction($reference);

        if (! ($verification['status'] ?? false) || $verification['data']['status'] !== 'success') {
            Log::warning("Paystack webhook: verification failed for {$reference}");
            abort(400);
        }

        $order = Order::where('paystack_reference', $reference)->first();

        if (! $order) {
            Log::warning("Paystack webhook: no matching order for {$reference}");
            abort(404);
        }

        if ($order->status !== 'pending') {
            // Already processed — webhook can legitimately fire more than once
            return response()->json(['status' => 'already_processed']);
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'paid']);
            $order->statusHistory()->create(['status' => 'paid', 'changed_by' => null]);

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

        $order->load('user');
        $recipientEmail = $order->user->email ?? $order->guest_email;
        Mail::to($recipientEmail)->queue(new OrderConfirmationMail($order));

        $adminEmails = \App\Models\User::role(['super_admin', 'owner'])->pluck('email');
        Mail::to($adminEmails)->queue(new AdminOrderNotificationMail($order));
        return response()->json(['status' => 'success']);
    }
}
