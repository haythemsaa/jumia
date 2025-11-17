<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'images',
        'is_verified_purchase',
        'helpful_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'images' => 'array',
            'is_verified_purchase' => 'boolean',
            'helpful_count' => 'integer',
        ];
    }

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorResponse()
    {
        return $this->hasOne(ReviewResponse::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    // Helper methods
    public function approve()
    {
        $this->update(['status' => 'approved']);

        // Update product rating
        $this->product->updateRating();
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }

    public function markAsHelpful()
    {
        $this->increment('helpful_count');
    }

    public function addImages(array $images)
    {
        $this->images = array_merge($this->images ?? [], $images);
        $this->save();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
