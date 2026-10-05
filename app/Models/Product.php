<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
    use Spatie\Activitylog\Support\LogOptions;

class Product extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'status'];
    protected $appends = ['starting_price', 'primary_image_url'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('is_primary')->withTimestamps();
    }

    public function primaryCategory(): ?Category
    {
        return $this->categories->firstWhere('pivot.is_primary', true) ?? $this->categories->first();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }


    public function getStartingPriceAttribute(): ?string
    {
        // Cheapest in-stock variant; falls back to cheapest overall if everything's out of stock
        $variant = $this->variants->where('stock_quantity', '>', 0)->sortBy('price')->first()
            ?? $this->variants->sortBy('price')->first();

        return $variant?->price;
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $image = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        return $image?->url;
    }

    public function orderItems(): HasManyThrough
    {
        // withTrashedParents: still count sales of variants you have since removed
        return $this->hasManyThrough(OrderItem::class, ProductVariant::class, 'product_id', 'product_variant_id')
            ->withTrashedParents();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('product')
            ->logOnly(['name', 'slug', 'status'])
            ->logOnlyDirty();
    }
}
