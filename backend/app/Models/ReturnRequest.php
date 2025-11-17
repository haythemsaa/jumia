<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'user_id',
        'type',
        'reason',
        'description',
        'images',
        'status',
        'admin_notes',
        'return_label_url',
        'refund_amount',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'images' => 'array',
        'refund_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approve(string $adminNotes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'admin_notes' => $adminNotes,
        ]);
    }

    public function reject(string $adminNotes): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
        ]);
    }

    public function complete(float $refundAmount = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'refund_amount' => $refundAmount ?? $this->refund_amount,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
