<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_cost',
        'cost_per_kg',
        'estimated_days_min',
        'estimated_days_max',
        'available_regions',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'base_cost' => 'decimal:2',
            'cost_per_kg' => 'decimal:2',
            'estimated_days_min' => 'integer',
            'estimated_days_max' => 'integer',
            'available_regions' => 'array',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    public function scopeForRegion($query, $region)
    {
        return $query->where(function ($q) use ($region) {
            $q->whereNull('available_regions')
                ->orWhereJsonContains('available_regions', $region);
        });
    }

    // Helper methods
    public function calculateCost($weight = 0)
    {
        return $this->base_cost + ($weight * $this->cost_per_kg);
    }

    public function getEstimatedDelivery()
    {
        $min = now()->addDays($this->estimated_days_min)->format('d/m/Y');
        $max = now()->addDays($this->estimated_days_max)->format('d/m/Y');

        return "{$min} - {$max}";
    }

    public function isAvailableForRegion($region)
    {
        if (empty($this->available_regions)) {
            return true; // Available everywhere
        }

        return in_array($region, $this->available_regions);
    }
}
