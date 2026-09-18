<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'full_name', 'guest_email', 'guest_phone', 'full_name', 'fulfillment_type',
        'pickup_point_id', 'delivery_address', 'status',
        'total_amount', 'received_at', 'received_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    // Accessors below let the frontend just check order.is_cancellable /
    // order.is_receivable rather than re-implementing this list in Vue.
    protected $appends = ['is_cancellable', 'is_receivable'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pickupPoint(): BelongsTo
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function successfulPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', 'success')->latestOfMany();
    }

    /**
     * A customer can still back out before the order has actually left
     * the pharmacy — once it's ready for pickup / out for delivery, or
     * finished, or already dead, cancelling no longer makes sense.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'paid', 'processing'], true);
    }

    /**
     * "Received" only makes sense once the order has actually been
     * dispatched to the customer in some form.
     */
    public function isReceivable(): bool
    {
        return in_array($this->status, ['ready_for_pickup', 'out_for_delivery'], true);
    }

    public function getIsCancellableAttribute(): bool
    {
        return $this->isCancellable();
    }

    public function getIsReceivableAttribute(): bool
    {
        return $this->isReceivable();
    }
}
