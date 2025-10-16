<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Shipping Model
 * 
 * Handles shipping options and pricing
 */
class Shipping extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'price',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float'
    ];
}
