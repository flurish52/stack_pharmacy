<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use App\Services\PushNotificationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:check-low-stock')]
#[Description('Command description')]
class CheckLowStock extends Command
{
    /**
     * Execute the console command.
     */
    protected $signature = 'stock:check-low';

    public function handle(PushNotificationService $push): void
    {
        ProductVariant::where('stock_quantity', '<=', config('pharmacy.low_stock_threshold'))
            ->where('stock_quantity', '>', 0)
            ->each(function ($variant) use ($push) {
                $push->notifyAdmins(
                    'Low Stock Reminder',
                    "{$variant->product->name} ({$variant->variant_name}) now has only {$variant->stock_quantity} units.",
                );
            });
    }
}
