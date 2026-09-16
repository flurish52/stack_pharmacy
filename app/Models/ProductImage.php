<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use SoftDeletes;
    protected $fillable = ['product_id', 'product_variant_id', 'cloudinary_public_id', 'is_primary'];
    protected $appends = ['url'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    
    public function getUrlAttribute(): string
    {
        return app(\App\Services\CloudinaryService::class)->url($this->cloudinary_public_id);
    }
}
