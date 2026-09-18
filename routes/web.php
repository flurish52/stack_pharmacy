<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactChannelController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\PickupPointController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC (storefront)
|--------------------------------------------------------------------------
*/

Route::get('/', [WelcomeController::class, 'index'])->name('pharm.home');

// Shop
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'add'])->name('add');
    Route::patch('/{variant}', [CartController::class, 'update'])->name('update');
    Route::delete('/{variant}', [CartController::class, 'destroy'])->name('destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
    Route::get('/callback', [CheckoutController::class, 'callback'])->name('callback');
    Route::get('/pickup-points', [PickupPointController::class, 'activeList'])->name('pickup-points');
});

// Informational pages
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
Route::get('/contact', [ContactChannelController::class, 'index'])->name('contact.index');

// Webhooks
Route::post('/webhooks/paystack', [PaystackWebhookController::class, 'handle']);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED (any logged-in user)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');

    // Pay for an existing order
    Route::get('/orders/{order}/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Push notifications
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store']);
    Route::delete('/push/subscribe', [PushSubscriptionController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| CUSTOMER ACCOUNT (auth + verified)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('account')
    ->name('account.')
    ->group(function () {

        // Orders
        Route::get('/orders', [CustomerController::class, 'ordersIndex'])->name('orders.index');
        Route::get('/orders/{order}', [CustomerController::class, 'ordersShow'])->name('orders.show');
        Route::patch('/orders/{order}/cancel', [CustomerController::class, 'cancelOrder'])->name('orders.cancel');
        Route::patch('/orders/{order}/received', [CustomerController::class, 'markReceived'])->name('orders.received');

        // Addresses
        Route::get('/addresses', [CustomerController::class, 'addressesIndex'])->name('addresses.index');
        Route::post('/addresses', [CustomerController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [CustomerController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [CustomerController::class, 'destroy'])->name('addresses.destroy');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
| Everything is under /admin/* with route names prefixed "admin."
*/

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Orders
        // NOTE: no permission gate here (same as your original). Consider
        // adding a `can:manage-orders` gate if only certain staff should access these.
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
            Route::patch('/{order}/received', [OrderController::class, 'markReceived'])->name('received');
            Route::patch('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        });

        // Products & product images
        Route::middleware('can:manage-products')->group(function () {
            Route::resource('products', ProductController::class)->except(['show']);
            Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
            Route::delete('product-images/{image}', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
        });

        // Categories
        Route::middleware('can:manage-categories')->group(function () {
            Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        // Pickup points
        Route::middleware('can:manage-pickup-points')->group(function () {
            Route::resource('pickup-points', PickupPointController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        // Services
        Route::middleware('can:manage-services')->prefix('services')->name('services.')->group(function () {
            Route::get('/', [ServiceController::class, 'adminIndex'])->name('index');
            Route::post('/', [ServiceController::class, 'store'])->name('store');
            Route::patch('/{service}', [ServiceController::class, 'update'])->name('update');
            Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
        });

        // Training page
        Route::middleware('can:manage-training')->prefix('training')->name('training.')->group(function () {
            Route::get('/', [TrainingController::class, 'edit'])->name('edit');
            Route::patch('/', [TrainingController::class, 'update'])->name('update');
        });

        // Contact channels
        Route::middleware('can:manage-contact')->prefix('contact-channels')->name('contact-channels.')->group(function () {
            Route::get('/', [ContactChannelController::class, 'adminIndex'])->name('index');
            Route::post('/', [ContactChannelController::class, 'store'])->name('store');
            Route::patch('/{contactChannel}', [ContactChannelController::class, 'update'])->name('update');
            Route::delete('/{contactChannel}', [ContactChannelController::class, 'destroy'])->name('destroy');
        });
    });


require __DIR__ . '/auth.php';
