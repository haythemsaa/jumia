<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'compare_price',
        'stock',
        'barcode',
        'weight',
        'attributes',
        'image',
        'is_default',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_default' => 'boolean',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get attribute labels
     */
    public function getAttributeLabelsAttribute(): array
    {
        $labels = [];
        
        foreach ($this->attributes as $attributeId => $valueId) {
            $value = AttributeValue::with('attribute')->find($valueId);
            if ($value) {
                $labels[$value->attribute->name] = $value->value;
            }
        }

        return $labels;
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute(): ?float
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return round((($this->compare_price - $this->price) / $this->compare_price) * 100, 2);
        }

        return null;
    }
}
