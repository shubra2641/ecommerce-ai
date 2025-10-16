<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Notification Model
 * 
 * Handles system notifications
 */
class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data',
        'type',
        'notifiable',
        'read_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];
}