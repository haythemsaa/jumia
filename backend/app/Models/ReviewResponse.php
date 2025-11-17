<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewResponse extends Model
{
    protected $fillable = [
        'product_review_id',
        'vendor_id',
        'response',
    ];

    public function productReview(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
