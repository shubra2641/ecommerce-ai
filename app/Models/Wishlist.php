<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Wishlist Model
 * 
 * Handles user wishlist functionality
 */
class Wishlist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'cart_id',
        'price',
        'amount',
        'quantity'
    ];

    /**
     * Get the product that owns the wishlist item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
