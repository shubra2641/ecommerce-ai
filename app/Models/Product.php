<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;
use App\Models\Traits\HasTranslations;
use App\Models\Category;
use App\Models\ProductReview;
use Illuminate\Support\Str;

/**
 * Product Model
 * 
 * Handles product management with translation support
 */
class Product extends Model
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
        'description',
        'cat_id',
        'child_cat_id',
        'price',
        'brand_id',
        'discount',
        'status',
        'photo',
        'size',
        'stock',
        'is_featured',
        'condition',
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
        'translations' => 'array',
        'price' => 'float',
        'discount' => 'float',
        'stock' => 'integer',
        'is_featured' => 'boolean'
    ];

    /**
     * Get the category that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cat_info()
    {
        return $this->hasOne(Category::class, 'id', 'cat_id');
    }

    /**
     * Get the sub-category that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sub_cat_info()
    {
        return $this->hasOne(Category::class, 'id', 'child_cat_id');
    }

    /**
     * Get all products with pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAllProduct()
    {
        return self::with(['cat_info', 'sub_cat_info'])->orderBy('id', 'desc')->paginate(10);
    }
    /**
     * Get related products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rel_prods()
    {
        return $this->hasMany(Product::class, 'cat_id', 'cat_id')->where('status', 'active')->orderBy('id', 'DESC')->limit(8);
    }

    /**
     * Get product reviews.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getReview()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id')->with('user_info')->where('status', 'active')->orderBy('id', 'DESC');
    }

    /**
     * Get product variants.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }
    /**
     * Get product by slug with translation support.
     *
     * @param string $slug
     * @return \App\Models\Product|null
     */
    public static function getProductBySlug($slug)
    {
        $product = self::with(['cat_info', 'rel_prods', 'getReview'])->where('slug', $slug)->first();
        if (!$product) {
            // try to find by translations field (title) if slug was generated from translated title
            $products = self::with(['cat_info', 'rel_prods', 'getReview'])->whereNotNull('translations')->get();
            foreach ($products as $p) {
                $translations = $p->translations ?? [];
                foreach ($translations as $locale => $fields) {
                    if (isset($fields['title']) && Str::slug($fields['title']) === $slug) {
                        return $p;
                    }
                }
            }
        }
        return $product;
    }
    /**
     * Count active products.
     *
     * @return int
     */
    public static function countActiveProduct()
    {
        $data = self::where('status', 'active')->count();
        return $data ?: 0;
    }

    /**
     * Get carts for this product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carts()
    {
        return $this->hasMany(Cart::class)->whereNotNull('order_id');
    }

    /**
     * Get wishlists for this product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class)->whereNotNull('cart_id');
    }

    /**
     * Get the brand that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

}