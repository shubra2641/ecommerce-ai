<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTranslations;

/**
 * Post Model
 * 
 * Handles blog posts with translation support
 */
class Post extends Model
{
    use HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'tags',
        'summary',
        'slug',
        'description',
        'photo',
        'quote',
        'post_cat_id',
        'post_tag_id',
        'added_by',
        'status',
        'translations'
    ];

    /**
     * Fields that should be translated
     *
     * @var array
     */
    protected $translatable = ['title', 'summary', 'description'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'translations' => 'array'
    ];


    /**
     * Get the category that owns the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cat_info()
    {
        return $this->hasOne(PostCategory::class, 'id', 'post_cat_id');
    }

    /**
     * Get the tag that owns the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tag_info()
    {
        return $this->hasOne(PostTag::class, 'id', 'post_tag_id');
    }

    /**
     * Get the author who created the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function author_info()
    {
        return $this->hasOne(User::class, 'id', 'added_by');
    }

    /**
     * Get all posts with pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAllPost()
    {
        return self::with(['cat_info', 'author_info'])->orderBy('id', 'DESC')->paginate(10);
    }
    // public function get_comments(){
    //     return $this->hasMany('App\Models\PostComment','post_id','id');
    // }
    /**
     * Get post by slug.
     *
     * @param string $slug
     * @return \App\Models\Post|null
     */
    public static function getPostBySlug($slug)
    {
        return self::with(['tag_info', 'author_info'])->where('slug', $slug)->where('status', 'active')->first();
    }

    /**
     * Get post comments (parent comments only).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class)->whereNull('parent_id')->where('status', 'active')->with('user_info')->orderBy('id', 'DESC');
    }

    /**
     * Get all post comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allComments()
    {
        return $this->hasMany(PostComment::class)->where('status', 'active');
    }


    // public static function getProductByCat($slug){
    //     // dd($slug);
    //     return Category::with('products')->where('slug',$slug)->first();
    //     // return Product::where('cat_id',$id)->where('child_cat_id',null)->paginate(10);
    // }

    // public static function getBlogByCategory($id){
    //     return Post::where('post_cat_id',$id)->paginate(8);
    // }
    /**
     * Get posts by tag.
     *
     * @param string $slug
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getBlogByTag($slug)
    {
        return self::where('tags', $slug)->paginate(8);
    }

    /**
     * Count active posts.
     *
     * @return int
     */
    public static function countActivePost()
    {
        $data = self::where('status', 'active')->count();
        return $data ?: 0;
    }
}