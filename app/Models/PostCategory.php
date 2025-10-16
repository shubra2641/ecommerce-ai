<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use App\Models\Traits\HasTranslations;

/**
 * Post Category Model
 * 
 * Handles blog post categories with translation support
 */
class PostCategory extends Model
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
     * Get active posts for this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function post()
    {
        return $this->hasMany(Post::class, 'post_cat_id', 'id')->where('status', 'active');
    }

    /**
     * Get all posts for this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'post_cat_id', 'id');
    }

    /**
     * Get blog posts by category slug.
     *
     * @param string $slug
     * @return \App\Models\PostCategory|null
     */
    public static function getBlogByCategory($slug)
    {
        return self::with('post')->where('slug', $slug)->first();
    }
}
