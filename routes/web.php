<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactChannelController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\PickupPointController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TrainingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::get('/checkout/callback', [CheckoutController::class, 'callback'])->name('checkout.callback');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::post('/webhooks/paystack', [PaystackWebhookController::class, 'handle']);

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{variant}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware(['auth', 'can:manage-products'])->group(function () {
    Route::post('/admin/products/{product}/images', [ProductImageController::class, 'store'])->name('admin.products.images.store');
    Route::delete('/admin/product-images/{image}', [ProductImageController::class, 'destroy'])->name('admin.product-images.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::post('/push/subscribe', [PushSubscriptionController::class, 'store']);
        Route::delete('/push/subscribe', [PushSubscriptionController::class, 'destroy']);
});

// routes/web.php (or admin.php if you split them)
Route::middleware(['auth'])->group(function () {

    Route::middleware('can:manage-products')->group(function () {
        Route::resource('admin/products', ProductController::class)
            ->names('admin.products')
            ->except(['show']);
    });

    Route::prefix('admin/orders')->name('admin.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
        Route::patch('/{order}/received', [OrderController::class, 'markReceived'])->name('received');
        Route::patch('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });
});



// Public
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/contact', [ContactChannelController::class, 'index'])->name('contact.index');
Route::get('/training', [TrainingController::class, 'index'])->name('training.index');

// Admin
Route::middleware(['auth', 'can:manage-categories'])->group(function () {
    Route::resource('admin/categories', CategoryController::class)->names('admin.categories')->only(['index', 'store', 'update', 'destroy']);
});

Route::middleware(['auth', 'can:manage-services'])->group(function () {
    Route::get('/admin/services', [ServiceController::class, 'adminIndex'])->name('admin.services.index');
    Route::post('/admin/services', [ServiceController::class, 'store'])->name('admin.services.store');
    Route::patch('/admin/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
    Route::delete('/admin/services/{service}', [ServiceController::class, 'destroy'])->name('admin.services.destroy');
});

Route::middleware(['auth', 'can:manage-training'])->group(function () {
    Route::get('/admin/training', [TrainingController::class, 'edit'])->name('admin.training.edit');
    Route::patch('/admin/training', [TrainingController::class, 'update'])->name('admin.training.update');
});

Route::middleware(['auth', 'can:manage-contact'])->group(function () {
    Route::get('/admin/contact-channels', [ContactChannelController::class, 'adminIndex'])->name('admin.contact-channels.index');
    Route::post('/admin/contact-channels', [ContactChannelController::class, 'store'])->name('admin.contact-channels.store');
    Route::patch('/admin/contact-channels/{contactChannel}', [ContactChannelController::class, 'update'])->name('admin.contact-channels.update');
    Route::delete('/admin/contact-channels/{contactChannel}', [ContactChannelController::class, 'destroy'])->name('admin.contact-channels.destroy');
});

Route::middleware(['auth', 'can:manage-pickup-points'])->group(function () {
    Route::resource('admin/pickup-points', PickupPointController::class)->names('admin.pickup-points')->only(['index', 'store', 'update', 'destroy']);
});

Route::get('/checkout/pickup-points', [PickupPointController::class, 'activeList'])->name('checkout.pickup-points');

require __DIR__.'/auth.php';
