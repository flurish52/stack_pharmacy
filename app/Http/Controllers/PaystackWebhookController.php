<?php

namespace App\Http\Controllers;

use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Payment;
use App\Models\User;
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

        if ($payload['event'] === 'charge.failed') {
            $reference = $payload['data']['reference'];
            $payment = Payment::where('reference', $reference)->first();

            if ($payment && $payment->status === 'pending') {
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => $payload['data'],
                ]);
                $payment->order->update(['status' => 'payment_failed']);
            }

            return response()->json(['status' => 'ok']);
        }

        if ($payload['event'] !== 'charge.success') {
            return response()->json(['status' => 'ignored']);
        }

        $reference = $payload['data']['reference'];

        $verification = $this->paystack->verifyTransaction($reference);

        if (! ($verification['status'] ?? false) || $verification['data']['status'] !== 'success') {
            Log::warning("Paystack webhook: verification failed for {$reference}");
            abort(400);
        }

        $payment = Payment::where('reference', $reference)->first();

        if (! $payment) {
            Log::warning("Paystack webhook: no matching payment for {$reference}");
            abort(404);
        }

        $this->paystack->markSuccessful($payment, $verification['data']);

        return response()->json(['status' => 'success']);
    }
}
