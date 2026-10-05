<?php

namespace App\Jobs;

use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;

    public function __construct(
        public string $title,
        public string $body,
        public string $url = '/admin/dashboard',
    ) {}

    public function handle(PushNotificationService $push): void
    {
        $push->deliver($this->title, $this->body, $this->url);
    }
}
