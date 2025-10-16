<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Message Model
 * 
 * Handles contact messages and inquiries
 */
class Message extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'message',
        'email',
        'phone',
        'read_at',
        'photo',
        'subject'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'read_at' => 'datetime'
    ];
}