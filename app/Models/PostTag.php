<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTranslations;

/**
 * Post Tag Model
 * 
 * Handles blog post tags with translation support
 */
class PostTag extends Model
{
    use HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'status',
        'translations'
    ];

    /**
     * Fields that should be translated
     *
     * @var array
     */
    protected $translatable = ['title'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'translations' => 'array'
    ];

    /**
     * Get posts for this tag.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'post_tag_id', 'id');
    }
}
