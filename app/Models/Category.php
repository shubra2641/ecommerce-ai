<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTranslations;

/**
 * Category Model
 * 
 * Handles category management with translation support
 */
class Category extends Model
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
        'summary',
        'photo',
        'status',
        'is_parent',
        'parent_id',
        'added_by',
        'translations'
    ];

    /**
     * Fields that should be translated
     *
     * @var array
     */
    protected $translatable = ['title', 'summary'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'translations' => 'array'
    ];

    /**
     * Get the parent category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent_info()
    {
        return $this->hasOne(Category::class, 'id', 'parent_id');
    }

    /**
     * Get all categories with pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAllCategory()
    {
        return self::orderBy('id', 'DESC')->with('parent_info')->paginate(10);
    }

    /**
     * Shift child categories to parent.
     *
     * @param array $cat_id
     * @return int
     */
    public static function shiftChild($cat_id)
    {
        return self::whereIn('id', $cat_id)->update(['is_parent' => 1]);
    }

    /**
     * Get child categories by parent ID.
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public static function getChildByParentID($id)
    {
        return self::where('parent_id', $id)->orderBy('id', 'ASC')->pluck('title', 'id');
    }

    /**
     * Get child categories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function child_cat()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id')->where('status', 'active');
    }

    /**
     * Get all parent categories with children.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllParentWithChild()
    {
        return self::with('child_cat')->where('is_parent', 1)->where('status', 'active')->orderBy('title', 'ASC')->get();
    }

    /**
     * Get products for this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'cat_id', 'id')->where('status', 'active');
    }

    /**
     * Get sub-products for this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sub_products()
    {
        return $this->hasMany(Product::class, 'child_cat_id', 'id')->where('status', 'active');
    }
    /**
     * Get products by category slug.
     *
     * @param string $slug
     * @return \App\Models\Category|null
     */
    public static function getProductByCat($slug)
    {
        return self::with('products')->where('slug', $slug)->first();
    }

    /**
     * Get sub-products by category slug.
     *
     * @param string $slug
     * @return \App\Models\Category|null
     */
    public static function getProductBySubCat($slug)
    {
        return self::with('sub_products')->where('slug', $slug)->first();
    }

    /**
     * Count active categories.
     *
     * @return int
     */
    public static function countActiveCategory()
    {
        $data = self::where('status', 'active')->count();
        return $data ?: 0;
    }
}