<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /**
     * Statuses staff may set through the "update status" endpoint.
     * paid / received / cancelled are deliberately NOT here - they have
     * their own logic (webhook, markReceived, cancel).
     */
    public const SETTABLE_STATUSES = [
        'processing',
        'out_for_delivery',
        'ready_for_pickup',
        'completed',
    ];

    protected $fillable = [
        'user_id', 'full_name', 'guest_email', 'guest_phone', 'fulfillment_type',
        'pickup_point_id', 'delivery_address', 'status',
        'total_amount', 'received_at', 'received_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public const COUNTED_STATUSES = [
        'paid', 'processing', 'ready_for_pickup', 'out_for_delivery', 'received', 'completed',
    ];

    public function scopeCounted($query)
    {
        return $query->whereIn('status', self::COUNTED_STATUSES);
    }

    // The frontend checks these instead of re-implementing the rules in Vue.
    protected $appends = ['is_cancellable', 'is_receivable', 'allowed_next_statuses'];

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
     * Staff (cancel-order) can cancel until the order leaves the pharmacy.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'paid', 'processing'], true);
    }

    /**
     * A customer can only back out before processing starts. After that,
     * cancellation is staff-only and handled manually.
     */
    public function isCustomerCancellable(): bool
    {
        return in_array($this->status, ['pending', 'paid'], true);
    }

    /**
     * "Received" only makes sense once the order has been dispatched.
     */
    public function isReceivable(): bool
    {
        return in_array($this->status, ['ready_for_pickup', 'out_for_delivery'], true);
    }

    /**
     * The one place that defines the order lifecycle for status updates.
     *
     * paid -> processing -> (out_for_delivery | ready_for_pickup) -> completed
     */
    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            'paid' => ['processing'],
            'processing' => [$this->fulfillment_type === 'delivery' ? 'out_for_delivery' : 'ready_for_pickup'],
            'ready_for_pickup', 'out_for_delivery', 'received' => ['completed'],
            default => [],
        };
    }

    public function getIsCancellableAttribute(): bool
    {
        return $this->isCancellable();
    }

    public function getIsReceivableAttribute(): bool
    {
        return $this->isReceivable();
    }

    public function getAllowedNextStatusesAttribute(): array
    {
        return $this->allowedNextStatuses();
    }
}
