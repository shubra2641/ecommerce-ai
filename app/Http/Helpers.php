<?php

use App\Models\Message;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\PostCategory;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Shipping;
use App\Models\Cart;
use App\Models\Language;
use App\Models\Product;
use App\Models\Brand;
use App\Models\User;
use App\Models\ProductReview;
use App\Models\PostComment;
use App\Models\Post;
use App\Models\Settings;
use App\Models\Coupon;
use App\Models\PaymentGateway;
use App\Models\Banner;
use App\Models\ProductVariant;
use App\Models\StatusNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Helper class containing static methods for common application functionality
 * 
 * This class provides utility methods for:
 * - Message management
 * - Category operations
 * - Cart and wishlist operations
 * - Order calculations
 * - Data retrieval methods
 * 
 * @package App\Http
 */
class Helper
{
    /**
     * Get unread messages list
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function messageList()
    {
        return Message::whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all categories with parent-child relationships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllCategory()
    {
        $category = new Category();
        return $category->getAllParentWithChild();
    }

    /**
     * Generate header category menu HTML
     * 
     * @return void
     */
    public static function getHeaderCategory()
    {
        $category = new Category();
        $menu = $category->getAllParentWithChild();

        if ($menu) {
            echo '<li>';
            echo '<a href="javascript:void(0);">Category<i class="ti-angle-down"></i></a>';
            echo '<ul class="dropdown border-0 shadow">';
            
            foreach ($menu as $cat_info) {
                if ($cat_info->child_cat && $cat_info->child_cat->count() > 0) {
                    echo '<li><a href="' . route('product-cat', $cat_info->slug) . '">' . $cat_info->title . '</a>';
                    echo '<ul class="dropdown sub-dropdown border-0 shadow">';
                    
                    foreach ($cat_info->child_cat as $sub_menu) {
                        echo '<li><a href="' . route('product-sub-cat', [$cat_info->slug, $sub_menu->slug]) . '">' . $sub_menu->title . '</a></li>';
                    }
                    
                    echo '</ul>';
                    echo '</li>';
                } else {
                    echo '<li><a href="' . route('product-cat', $cat_info->slug) . '">' . $cat_info->title . '</a></li>';
                }
            }
            
            echo '</ul>';
            echo '</li>';
        }
    }

    /**
     * Get product category list
     * 
     * @param string $option Filter option ('all' or 'with_products')
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function productCategoryList($option = 'all')
    {
        if ($option === 'all') {
            return Category::orderBy('id', 'DESC')->get();
        }
        
        return Category::has('products')->orderBy('id', 'DESC')->get();
    }

    /**
     * Get post tag list
     * 
     * @param string $option Filter option ('all' or 'with_posts')
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function postTagList($option = 'all')
    {
        if ($option === 'all') {
            return PostTag::orderBy('id', 'desc')->get();
        }
        
        return PostTag::has('posts')->orderBy('id', 'desc')->get();
    }

    /**
     * Get post category list
     * 
     * @param string $option Filter option ('all' or 'with_posts')
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function postCategoryList($option = "all")
    {
        if ($option === 'all') {
            return PostCategory::orderBy('id', 'DESC')->get();
        }
        
        return PostCategory::has('posts')->orderBy('id', 'DESC')->get();
    }

    /**
     * Get cart count for authenticated user
     * 
     * @param string $user_id User ID (optional)
     * @return int
     */
    public static function cartCount($user_id = '')
    {
        if (!Auth::check()) {
            return 0;
        }

        if (empty($user_id)) {
            $user_id = auth()->user()->id;
        }

        return Cart::where('user_id', $user_id)
            ->where('order_id', null)
            ->sum('quantity');
    }

    /**
     * Get all products from cart for authenticated user
     * 
     * @param string $user_id User ID (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllProductFromCart($user_id = '')
    {
        if (!Auth::check()) {
            return collect();
        }

        if (empty($user_id)) {
            $user_id = auth()->user()->id;
        }

        $carts = Cart::with('product')
            ->where('user_id', $user_id)
            ->where('order_id', null)
            ->get();
        
        // Process cart items with photo arrays
        return $carts->map(function($cart) {
            if ($cart->product && $cart->product->photo) {
                $cart->product->photo_array = explode(',', $cart->product->photo);
            }
            return $cart;
        });
    }

    /**
     * Calculate total cart price for authenticated user
     * 
     * @param string $user_id User ID (optional)
     * @return float
     */
    public static function totalCartPrice($user_id = '')
    {
        if (!Auth::check()) {
            return 0;
        }

        if (empty($user_id)) {
            $user_id = auth()->user()->id;
        }

        return Cart::where('user_id', $user_id)
            ->where('order_id', null)
            ->sum('amount');
    }

    /**
     * Get wishlist count for authenticated user
     * 
     * @param string $user_id User ID (optional)
     * @return int
     */
    public static function wishlistCount($user_id = '')
    {
        if (!Auth::check()) {
            return 0;
        }

        if (empty($user_id)) {
            $user_id = auth()->user()->id;
        }

        return Wishlist::where('user_id', $user_id)
            ->where('cart_id', null)
            ->sum('quantity');
    }

    /**
     * Get all products from wishlist for authenticated user
     * 
     * @param string $user_id User ID (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllProductFromWishlist($user_id = '')
    {
        if (!Auth::check()) {
            return collect();
        }

        if (empty($user_id)) {
            $user_id = auth()->user()->id;
        }

        $wishlists = Wishlist::with('product')
            ->where('user_id', $user_id)
            ->where('cart_id', null)
            ->get();
        
        // Process wishlist items with photo arrays
        return $wishlists->map(function($wishlist) {
            if ($wishlist->product && $wishlist->product->photo) {
                $wishlist->product->photo_array = explode(',', $wishlist->product->photo);
            }
            return $wishlist;
        });
    }

    /**
     * Calculate total wishlist price for authenticated user
     * 
     * @param string $user_id User ID (optional)
     * @return float
     */
    public static function totalWishlistPrice($user_id = '')
    {
        if (!Auth::check()) {
            return 0;
        }

        if (empty($user_id)) {
            $user_id = auth()->user()->id;
        }

        return Wishlist::where('user_id', $user_id)
            ->where('cart_id', null)
            ->sum('amount');
    }

    /**
     * Calculate grand total price including shipping
     * 
     * @param int $id Order ID
     * @param string $user_id User ID
     * @return float
     */
    public static function grandPrice($id, $user_id)
    {
        $order = Order::find($id);
        if (!$order) {
            return 0;
        }

        $shipping_price = (float) ($order->shipping->price ?? 0);
        $order_price = self::orderPrice($id, $user_id);
        
        return number_format((float) ($order_price + $shipping_price), 2, '.', '');
    }

    /**
     * Calculate order price
     * 
     * @param int $id Order ID
     * @param string $user_id User ID
     * @return float
     */
    public static function orderPrice($id, $user_id)
    {
        $order = Order::find($id);
        if (!$order) {
            return 0;
        }

        return $order->cart_info->sum('price');
    }

    /**
     * Calculate total earnings per month from delivered orders
     * 
     * @return float
     */
    public static function earningPerMonth()
    {
        $month_data = Order::where('status', 'delivered')->get();
        $price = 0;
        
        foreach ($month_data as $data) {
            $price += $data->cart_info->sum('price');
        }
        
        return number_format((float) $price, 2, '.', '');
    }

    /**
     * Get all shipping methods
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function shipping()
    {
        return Shipping::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all products with relationships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllProduct()
    {
        return Product::with(['category', 'brand', 'reviews'])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Get all brands
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllBrand()
    {
        return Brand::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all users
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllUser()
    {
        return User::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all orders with relationships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllOrder()
    {
        return Order::with(['carts.product', 'carts.variant', 'user', 'shipping'])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Get all wishlists with relationships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllWishlist()
    {
        return Wishlist::with(['product', 'user'])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Get all product reviews with relationships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllReview()
    {
        return ProductReview::with(['user', 'product'])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Get all post comments with relationships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllComment()
    {
        return PostComment::with(['user', 'post'])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Get all posts with relationships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllPost()
    {
        return Post::with(['category', 'tag', 'addedBy'])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Get all messages
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllMessage()
    {
        return Message::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all settings
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllSettings()
    {
        return Settings::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all coupons
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllCoupon()
    {
        return Coupon::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all payment gateways
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllPaymentGateway()
    {
        return PaymentGateway::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all banners
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllBanner()
    {
        return Banner::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all product variants with relationships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllProductVariant()
    {
        return ProductVariant::with(['product'])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Get all post categories
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllPostCategory()
    {
        return PostCategory::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all post tags
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllPostTag()
    {
        return PostTag::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all languages
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllLanguage()
    {
        return Language::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all notifications
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllNotification()
    {
        return \Illuminate\Notifications\DatabaseNotification::orderBy('id', 'DESC')->get();
    }

    /**
     * Get all status notifications
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllStatusNotification()
    {
        return StatusNotification::orderBy('id', 'DESC')->get();
    }

    /**
     * Get compare count for current user or session
     * 
     * @return int
     */
    public static function compareCount()
    {
        try {
            $userId = auth()->id();
            $sessionId = session()->getId();
            
            return \App\Models\Compare::getCompareItems($userId, $sessionId)->count();
        } catch (\Exception $e) {
            Log::error('Error getting compare count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all products from compare list
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllProductFromCompare()
    {
        try {
            $userId = auth()->id();
            $sessionId = session()->getId();
            
            return \App\Models\Compare::getCompareItems($userId, $sessionId);
        } catch (\Exception $e) {
            Log::error('Error getting compare products: ' . $e->getMessage());
            return collect();
        }
    }
}

// Global Helper Functions

if (!function_exists('safe_html')) {
    /**
     * Sanitize HTML for safe rendering in views.
     * Uses HTMLPurifier if available, otherwise falls back to a conservative sanitizer.
     * Returns an \Illuminate\Support\HtmlString so Blade can render it without additional escaping.
     *
     * @param string|null $html
     * @return \Illuminate\Support\HtmlString
     */
    function safe_html($html)
    {
        if (empty($html)) {
            return new \Illuminate\Support\HtmlString('');
        }

        // Prefer a cached HTMLPurifier instance when available.
        try {
            if (class_exists('\HTMLPurifier')) {
                static $purifier = null;

                if ($purifier === null) {
                    $config = \HTMLPurifier_Config::createDefault();

                    // Merge user-specified config from config/sanitizer.php if available
                    $appConfig = config('sanitizer.htmlpurifier', []);
                    foreach ($appConfig as $key => $value) {
                        $config->set($key, $value);
                    }

                    $purifier = new \HTMLPurifier($config);
                }

                $clean = $purifier->purify($html);
                return new \Illuminate\Support\HtmlString($clean);
            }
        } catch (\Exception $e) {
            // If HTMLPurifier exists but fails, fall back to safe sanitizer below and log the error
            Log::warning('HTMLPurifier error: ' . $e->getMessage());
        }

        // Basic fallback sanitizer: allow configured tags, remove event handlers and javascript: URIs
        $allowedTags = config('sanitizer.allowed_html', '<p><br><strong><b><em><i><ul><ol><li><a>');
        $clean = strip_tags($html, $allowedTags);

        // Remove on* attributes (onclick, onmouseover, etc.)
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $clean);

        // Sanitize href attributes to avoid javascript: URIs
        $clean = preg_replace_callback('/<a[^>]+href\s*=\s*("|\')([^"\']+)("|\')[^>]*>/i', function ($m) {
            $href = trim($m[2]);
            if (preg_match('/^javascript\:/i', $href)) {
                return '<a href="#">';
            }
            // Encode URL to avoid injection
            return '<a href="' . e($href) . '">';
        }, $clean);

        return new \Illuminate\Support\HtmlString($clean);
    }
}

if (!function_exists('generateUniqueSlug')) {
    /**
     * Generate a unique slug for a given title and model.
     *
     * @param string $title
     * @param string $modelClass
     * @return string
     */
    function generateUniqueSlug($title, $modelClass)
    {
        $slug = Str::slug($title);
        $count = $modelClass::where('slug', $slug)->count();

        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }

        return $slug;
    }
}

// Language Helper Functions

if (!function_exists('getCurrentLanguage')) {
    /**
     * Get current language
     * Returns the currently active language object
     * 
     * @return \App\Models\Language|null
     */
    function getCurrentLanguage()
    {
        $languageCode = Session::get('locale', config('app.locale'));
        return Language::getByCode($languageCode);
    }
}

if (!function_exists('getCurrentLanguageCode')) {
    /**
     * Get current language code
     * Returns the currently active language code (e.g., 'en', 'ar')
     * 
     * @return string
     */
    function getCurrentLanguageCode()
    {
        return Session::get('locale', config('app.locale'));
    }
}

if (!function_exists('getCurrentDirection')) {
    /**
     * Get current text direction
     * Returns 'ltr' or 'rtl' based on current language
     * 
     * @return string
     */
    function getCurrentDirection()
    {
        return Session::get('text_direction', 'ltr');
    }
}

if (!function_exists('getActiveLanguages')) {
    /**
     * Get all active languages
     * Returns collection of active languages
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveLanguages()
    {
        return Language::getActive();
    }
}

if (!function_exists('getDefaultLanguage')) {
    /**
     * Get default language
     * Returns the default language object
     * 
     * @return \App\Models\Language|null
     */
    function getDefaultLanguage()
    {
        return Language::getDefault();
    }
}

if (!function_exists('switchLanguage')) {
    /**
     * Switch language
     * Changes the current language and updates session
     * 
     * @param string $languageCode
     * @return bool
     */
    function switchLanguage($languageCode)
    {
        $language = Language::getByCode($languageCode);
        
        if ($language && $language->is_active) {
            Session::put('locale', $languageCode);
            Session::put('text_direction', $language->direction);
            app()->setLocale($languageCode);
            return true;
        }
        
        return false;
    }
}

if (!function_exists('getCurrentFlag')) {
    /**
     * Get language flag
     * Returns the flag code for current language
     * 
     * @return string
     */
    function getCurrentFlag()
    {
        $language = getCurrentLanguage();
        return $language ? $language->flag : 'us';
    }
}

if (!function_exists('isRTL')) {
    /**
     * Check if current language is RTL
     * Returns true if current language is right-to-left
     * 
     * @return bool
     */
    function isRTL()
    {
        return getCurrentDirection() === 'rtl';
    }
}

if (!function_exists('getCurrentLanguageName')) {
    /**
     * Get language name
     * Returns the name of current language
     * 
     * @return string
     */
    function getCurrentLanguageName()
    {
        $language = getCurrentLanguage();
        return $language ? $language->name : 'English';
    }
}

if (!function_exists('getLanguageSwitcherData')) {
    /**
     * Generate language switcher data
     * Returns array of languages for language switcher component
     * Current language appears first in the list
     * 
     * @return \Illuminate\Support\Collection
     */
    function getLanguageSwitcherData()
    {
        $languages = getActiveLanguages();
        $currentCode = getCurrentLanguageCode();
        
        $languageData = $languages->map(function($language) use ($currentCode) {
            return [
                'code' => $language->code,
                'name' => $language->name,
                'flag' => $language->flag,
                'direction' => $language->direction,
                'is_current' => $language->code === $currentCode,
                'url' => request()->fullUrlWithQuery(['lang' => $language->code])
            ];
        });
        
        // Sort so current language appears first
        return $languageData->sortByDesc('is_current');
    }
}

if (!function_exists('transWithFallback')) {
    /**
     * Get translation with fallback
     * Returns translation for given key with fallback to default language
     * 
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    function transWithFallback($key, $replace = [], $locale = null)
    {
        $locale = $locale ?: getCurrentLanguageCode();
        
        // Try to get translation in current language
        $translation = trans($key, $replace, $locale);
        
        // If translation is same as key (not found), try default language
        if ($translation === $key) {
            $defaultLanguage = getDefaultLanguage();
            if ($defaultLanguage && $defaultLanguage->code !== $locale) {
                $translation = trans($key, $replace, $defaultLanguage->code);
            }
        }
        
        return $translation;
    }
}

if (!function_exists('transChoiceWithFallback')) {
    /**
     * Get translation choice with fallback
     * Returns translation choice for given key with fallback to default language
     * 
     * @param string $key
     * @param int $number
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    function transChoiceWithFallback($key, $number, $replace = [], $locale = null)
    {
        $locale = $locale ?: getCurrentLanguageCode();
        
        // Try to get translation choice in current language
        $translation = trans_choice($key, $number, $replace, $locale);
        
        // If translation is same as key (not found), try default language
        if ($translation === $key) {
            $defaultLanguage = getDefaultLanguage();
            if ($defaultLanguage && $defaultLanguage->code !== $locale) {
                $translation = trans_choice($key, $number, $replace, $defaultLanguage->code);
            }
        }
        
        return $translation;
    }
}

if (!function_exists('getSiteName')) {
    /**
     * Get site name based on current language
     * Returns translated site name or fallback to default
     * 
     * @return string
     */
    function getSiteName()
    {
        try {
            $settings = Settings::first();
            if ($settings && !empty($settings->site_name)) {
                return $settings->site_name;
            }
            return config('app.name', 'Laravel');
        } catch (\Exception $e) {
            Log::error('Error getting site name: ' . $e->getMessage());
            return config('app.name', 'Laravel');
        }
    }
}

if (!function_exists('getPageTitle')) {
    /**
     * Generate page title with site name
     * Returns formatted title: "Page Title | Site Name"
     * 
     * @param string|null $pageTitle
     * @return string
     */
    function getPageTitle($pageTitle = null)
    {
        $siteName = getSiteName();
        
        if (!empty($pageTitle)) {
            return $pageTitle . ' | ' . $siteName;
        }
        
        return $siteName;
    }
}
