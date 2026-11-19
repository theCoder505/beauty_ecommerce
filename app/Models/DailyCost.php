<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_date',
        'title',
        'category',
        'amount',
        'product_id',
        'quantity',
        'unit_cost',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'cost_date' => 'date:Y-m-d',
        'amount'    => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'quantity'  => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'inventory'          => 'Inventory Procurement',
            'packaging'          => 'Packaging Materials',
            'shipping_logistics' => 'Shipping & Logistics',
            'operational'        => 'Operational Overhead',
            'marketing'          => 'Marketing & Ads',
            default              => ucfirst(str_replace('_', ' ', $this->category ?? 'Other')),
        };
    }

    public function getCategoryBadgeClassesAttribute(): string
    {
        return match($this->category) {
            'inventory'          => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
            'packaging'          => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'shipping_logistics' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/40 dark:text-cyan-300 dark:border-cyan-800',
            'operational'        => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
            'marketing'          => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            default              => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
        };
    }
}
