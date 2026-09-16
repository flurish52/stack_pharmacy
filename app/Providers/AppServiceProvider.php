<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactChannel;
use App\Models\Order;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\Service;
use App\Models\Training;
use App\Policies\CategoryPolicy;
use App\Policies\ContactChannelPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PickupPointPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ServicePolicy;
use App\Policies\TrainingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Training::class, TrainingPolicy::class);
        Gate::policy(ContactChannel::class, ContactChannelPolicy::class);
        Gate::policy(PickupPoint::class, PickupPointPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);

    }
}
