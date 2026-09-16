<?php

namespace App\Observers;

use App\Models\ProductImage;

class ProductImageObserver
{
    /**
     * Handle the ProductImage "created" event.
     */
    public function created(ProductImage $productImage): void
    {
        //
    }

    /**
     * Handle the ProductImage "updated" event.
     */
    public function updated(ProductImage $productImage): void
    {
        //
    }

    /**
     * Handle the ProductImage "deleted" event.
     */
    public function deleting(ProductImage $image): void
    {
        app(\App\Services\CloudinaryService::class)->destroy($image->cloudinary_public_id);
    }

    /**
     * Handle the ProductImage "restored" event.
     */
    public function restored(ProductImage $productImage): void
    {
        //
    }

    /**
     * Handle the ProductImage "force deleted" event.
     */
    public function forceDeleted(ProductImage $productImage): void
    {
        //
    }
}
