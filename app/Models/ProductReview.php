<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Product Review Model
 * 
 * Handles product reviews and ratings
 */
class ProductReview extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'rate',
        'review',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'rate' => 'integer'
    ];

    /**
     * Get the user who wrote the review.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user_info()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Get all reviews with pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAllReview()
    {
        return self::with('user_info')->paginate(10);
    }

    /**
     * Get all user reviews with pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAllUserReview()
    {
        return self::where('user_id', auth()->user()->id)->with('user_info')->paginate(10);
    }

    /**
     * Get the product that owns the review.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}