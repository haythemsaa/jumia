<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'thumbnail_path',
        'order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper methods
    public function getFullImageUrl()
    {
        return asset('storage/' . $this->image_path);
    }

    public function getThumbnailUrl()
    {
        return $this->thumbnail_path
            ? asset('storage/' . $this->thumbnail_path)
            : $this->getFullImageUrl();
    }

    public function setPrimary()
    {
        // Remove primary from all other images of this product
        self::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }
}
