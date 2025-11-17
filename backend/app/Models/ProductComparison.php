<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_ids',
    ];

    protected function casts(): array
    {
        return [
            'product_ids' => 'array',
        ];
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return Product::whereIn('id', $this->product_ids ?? [])->get();
    }

    // Helper methods
    public function addProduct(int $productId): bool
    {
        $productIds = $this->product_ids ?? [];

        if (!in_array($productId, $productIds)) {
            $productIds[] = $productId;
            $this->product_ids = $productIds;
            $this->save();
            return true;
        }

        return false;
    }

    public function removeProduct(int $productId): bool
    {
        $productIds = $this->product_ids ?? [];

        if (($key = array_search($productId, $productIds)) !== false) {
            unset($productIds[$key]);
            $this->product_ids = array_values($productIds);
            $this->save();
            return true;
        }

        return false;
    }

    public function hasProduct(int $productId): bool
    {
        return in_array($productId, $this->product_ids ?? []);
    }

    public function getProductCount(): int
    {
        return count($this->product_ids ?? []);
    }

    public function clear(): void
    {
        $this->product_ids = [];
        $this->save();
    }
}
