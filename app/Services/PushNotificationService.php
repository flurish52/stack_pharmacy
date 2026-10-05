<?php

namespace App\Services;

use App\Http\Middleware\EnsureAdminArea;
use App\Jobs\SendPushNotification;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    /**
     * Queue a push to every subscribed staff device. Returns immediately;
     * the queue worker does the actual sending.
     */
    public function notifyAdmins(string $title, string $body, string $url = '/admin/dashboard'): void
    {
        SendPushNotification::dispatch($title, $body, $url);
    }

    /** Sends right now. Called by the queued job (and handy in tinker). */
    public function deliver(string $title, string $body, string $url = '/admin/dashboard'): void
    {
        // Only people who still have admin-area access get alerts.
        $subscriptions = PushSubscription::query()
            ->whereHas('user', fn ($q) => $q->role(EnsureAdminArea::ROLES))
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('pharmacy.vapid.subject'),
                'publicKey' => config('pharmacy.vapid.public_key'),
                'privateKey' => config('pharmacy.vapid.private_key'),
            ],
        ], ['TTL' => 3600]);

        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url]);

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->keys['p256dh'] ?? null,
                    'authToken' => $subscription->keys['auth'] ?? null,
                    'contentEncoding' => 'aes128gcm',
                ]),
                $payload
            );
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                continue;
            }

            // 404/410: the browser removed the subscription, so forget it.
            if ($report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();

                continue;
            }

            Log::warning('Web push failed', [
                'endpoint' => $report->getEndpoint(),
                'reason' => $report->getReason(),
            ]);
        }
    }
}
