<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'flag',
        'direction',
        'is_default',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Scope a query to only include active languages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include default language.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get language by code.
     *
     * @param string $code
     * @return \App\Models\Language|null
     */
    public static function getByCode($code)
    {
        return static::where('code', $code)->first();
    }

    /**
     * Get all active languages.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive()
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Get default language.
     *
     * @return \App\Models\Language|null
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Set this language as the default language.
     *
     * @return void
     */
    public function setAsDefault()
    {
        // First, set all languages to not default
        static::where('is_default', true)->update(['is_default' => false]);

        // Then set this language as default
        $this->update(['is_default' => true]);
    }
}