<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    public function notifyAdmins(string $title, string $body, string $tag = 'stack-pharmacy', string $url = '/'): void
    {
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.subject'),
                'publicKey' => config('services.webpush.public_key'),
                'privateKey' => config('services.webpush.private_key'),
            ],
        ]);

        foreach (PushSubscription::all() as $subscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->keys['p256dh'],
                    'authToken' => $subscription->keys['auth'],
                ]),
                json_encode([
                    'title' => $title,
                    'body' => $body,
                    'tag' => 'low-stock',
                    'url' => '/admin/products',
                ])
            );
        }

        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                // Expired/invalid subscription — clean it up so we don't keep retrying a dead endpoint
                PushSubscription::where('endpoint', $report->getRequest()->getUri())->delete();
            }
        }
    }
}
