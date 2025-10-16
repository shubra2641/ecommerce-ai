<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order Model
 * 
 * Handles order management and processing
 */
class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'sub_total',
        'quantity',
        'delivery_charge',
        'status',
        'total_amount',
        'first_name',
        'last_name',
        'country',
        'post_code',
        'payment_method',
        'payment_status',
        'transaction_id',
        'payment_proof',
        'address1',
        'address2',
        'phone',
        'email',
        'payment_method',
        'payment_status',
        'shipping_id',
        'coupon'
    ];

    /**
     * Get cart items for this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cart_info()
    {
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }

    /**
     * Get cart items for this order (alias).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cart()
    {
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }

    /**
     * Get payment transactions for this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get the latest payment transaction for this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestTransaction()
    {
        return $this->hasOne(PaymentTransaction::class)->latest();
    }

    /**
     * Get all order data by ID.
     *
     * @param int $id
     * @return \App\Models\Order|null
     */
    public static function getAllOrder($id)
    {
        return self::with('cart_info')->find($id);
    }

    /**
     * Count total orders.
     *
     * @return int
     */
    public static function countActiveOrder()
    {
        $data = self::count();
        return $data ?: 0;
    }


    /**
     * Get shipping information for this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_id');
    }

    /**
     * Get user who placed this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}