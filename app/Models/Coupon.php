<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Coupon Model
 * 
 * Handles coupon and discount functionality
 */
class Coupon extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'status'
    ];

    /**
     * Find coupon by code.
     *
     * @param string $code
     * @return \App\Models\Coupon|null
     */
    public static function findByCode($code)
    {
        return self::where('code', $code)->first();
    }

    /**
     * Calculate discount amount.
     *
     * @param float $total
     * @return float
     */
    public function discount($total)
    {
        if ($this->type === 'fixed') {
            return $this->value;
        } elseif ($this->type === 'percent') {
            return ($this->value / 100) * $total;
        }
        
        return 0;
    }
}