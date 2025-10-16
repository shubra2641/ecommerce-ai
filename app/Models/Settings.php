<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTranslations;

/**
 * Settings Model
 * 
 * Handles application settings with translation support
 */
class Settings extends Model
{
    use HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'short_des',
        'description',
        'photo',
        'address',
        'phone',
        'email',
        'logo',
        'site_name',
        'translations'
    ];

    /**
     * Fields that should be translated
     *
     * @var array
     */
    protected $translatable = ['site_name'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'translations' => 'array'
    ];
}
