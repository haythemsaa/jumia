<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'vendor_id',
        'product_name',
        'product_sku',
        'variant_details',
        'quantity',
        'unit_price',
        'total_price',
        'vendor_commission',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'variant_details' => 'array',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'vendor_commission' => 'decimal:2',
        ];
    }

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Helper methods
    public function getVendorEarnings()
    {
        return $this->total_price - $this->vendor_commission;
    }
}
