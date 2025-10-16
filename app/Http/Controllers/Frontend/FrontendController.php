<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\Frontend\LoginRequest;
use App\Http\Requests\Frontend\RegisterRequest;
use App\Http\Requests\Frontend\NewsletterSubscribeRequest;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\Newsletter;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\Cart;
use App\Models\Brand;
use App\Models\User;
use App\Models\Settings;
use Exception;

/**
 * FrontendController handles all frontend page operations
 * 
 * This controller manages home page, product listings, blog posts, user authentication,
 * and other frontend functionality with secure validation and proper error handling.
 */
class FrontendController extends Controller
{
    public function __construct()
    {
        // share commonly needed data to views
        $this->shareCommonViewData();
    }
    /**
     * Process product data with photos, discounts, and ratings
     * 
     * @param mixed $products
     * @return mixed
     */
    private function processProductData($products)
    {
        try {
        return $products->map(function($product) {
                // Ensure photo_array exists even if photo is null
            if (isset($product->photo) && $product->photo !== null && $product->photo !== '') {
                $product->photo_array = explode(',', $product->photo);
                    $product->first_photo = $product->photo_array[0] ?? '';
            } else {
                $product->photo_array = [];
                    $product->first_photo = '';
                }
                
                // Calculate after discount price
                $product->after_discount = $this->calculateAfterDiscount($product->price, $product->discount);
                
                // Process sizes array
            $product->sizes_array = isset($product->size) && $product->size ? explode(',', $product->size) : [];
                
                // Add rating data if not already present
                if (!isset($product->rate)) {
                    $product->rate = $this->getProductRating($product->id);
                    $product->rate_count = $this->getProductRatingCount($product->id);
                }
                
            return $product;
        });
        } catch (Exception $e) {
            \Log::error('Error processing product data: ' . $e->getMessage());
            return $products; // Return original data on error
        }
    }

    /**
     * Process product photos only
     * 
     * @param mixed $products
     * @return mixed
     */
    private function processProductPhotos($products)
    {
        try {
            return $products->map(function($product) {
                if (isset($product->photo) && $product->photo !== null && $product->photo !== '') {
                    $product->photo_array = explode(',', $product->photo);
                    $product->first_photo = $product->photo_array[0] ?? '';
                } else {
                    $product->photo_array = [];
                    $product->first_photo = '';
                }
            return $product;
        });
        } catch (Exception $e) {
            \Log::error('Error processing product photos: ' . $e->getMessage());
            return $products;
        }
    }

    /**
     * Calculate after discount price
     * 
     * @param float $price
     * @param float $discount
     * @return float
     */
    private function calculateAfterDiscount(float $price, float $discount): float
    {
        return $price - (($price * $discount) / 100);
    }

    /**
     * Get product rating average
     * 
     * @param int $productId
     * @return float
     */
    private function getProductRating(int $productId): float
    {
        try {
            return (float) DB::table('product_reviews')
                ->where('product_id', $productId)
                ->avg('rate') ?? 0;
        } catch (Exception $e) {
            \Log::error('Error getting product rating: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get product rating count
     * 
     * @param int $productId
     * @return int
     */
    private function getProductRatingCount(int $productId): int
    {
        try {
            return (int) DB::table('product_reviews')
                ->where('product_id', $productId)
                ->count();
        } catch (Exception $e) {
            \Log::error('Error getting product rating count: ' . $e->getMessage());
            return 0;
        }
    }
   
    /**
     * Redirect authenticated user to their role-based dashboard
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return redirect()->route('home');
            }
            
            $role = $user->role ?? 'user';
            return redirect()->route($role);
            
        } catch (Exception $e) {
            \Log::error('Error in index redirect: ' . $e->getMessage());
            return redirect()->route('home');
        }
    }

    /**
     * Display the home page with featured products, posts, and banners
     * 
     * @return View
     */
    public function home(): View
    {
        try {
            // Base query to keep logic consistent with productGrids (locale handled via HasTranslations trait)
            $baseProductsQuery = Product::query()->where('status', 'active');

            // Featured products (reuse base query for consistency)
            $featured = (clone $baseProductsQuery)
                ->where('is_featured', 1)
                ->orderBy('price', 'DESC')
                ->limit(2)
                ->get();

            // Recent posts (Posts also use HasTranslations)
            $posts = Post::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            // Get active banners
            $banners = Banner::where('status', 'active')
                ->limit(3)
                ->orderBy('id', 'DESC')
                ->get();
            
            // Recent products (same approach as productGrids minus filters, limited for homepage)
            $products = (clone $baseProductsQuery)
                ->orderBy('id', 'DESC')
                ->limit(8)
                ->get();
            
            // Get parent categories
            $category = Category::where('status', 'active')
                ->where('is_parent', 1)
                ->orderBy('title', 'ASC')
                ->get();
            
            // Get category lists using Eloquent instead of raw SQL
            $category_lists = Category::where('status', 'active')
                ->limit(3)
                ->get();
            
            $categories = Category::where('status', 'active')
                ->where('is_parent', 1)
                ->get();
            
            // Get settings
            $settings = Settings::first();
            
            // Product lists (another slice for a section on homepage)
            $product_lists = (clone $baseProductsQuery)
                ->orderBy('id', 'DESC')
                ->limit(6)
                ->get();

            // Process all product sets (ensures discount, photos, ratings localized attributes resolved on access)
            $product_lists = $this->processProductData($product_lists);
            $products_with_ratings = $this->processProductData($products);
            $featured = $this->processProductData($featured); // previously only photos; now include discount & rating
            
            return view('frontend.index', compact(
                'featured', 'posts', 'banners', 'products', 'category', 
                'category_lists', 'categories', 'settings', 'product_lists', 
                'products_with_ratings'
            ));
            
        } catch (Exception $e) {
            \Log::error('Error loading home page: ' . $e->getMessage());
            
            // Return minimal data on error
            $settings = Settings::first();
            return view('frontend.index', [
                'featured' => collect(),
                'posts' => collect(),
                'banners' => collect(),
                'products' => collect(),
                'category' => collect(),
                'category_lists' => collect(),
                'categories' => collect(),
                'settings' => $settings,
                'product_lists' => collect(),
                'products_with_ratings' => collect()
            ]);
        }
    }   

    /**
     * Display the about us page
     * 
     * @return View
     */
    public function aboutUs(): View
    {
        try {
            $settings = Settings::first();
            
            // Get products for header
            $products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(8)
                ->get();
            
            // Process products with photos and discounts
            $products = $this->processProductData($products);
        
        return view('frontend.pages.about-us', compact('settings', 'products'));
            
        } catch (Exception $e) {
            \Log::error('Error loading about us page: ' . $e->getMessage());
            
            $settings = Settings::first();
            return view('frontend.pages.about-us', [
                'settings' => $settings,
                'products' => collect()
            ]);
        }
    }

    /**
     * Display the contact page
     * 
     * @return View
     */
    public function contact(): View
    {
        try {
            $settings = Settings::first();
            
            // Get products for header
            $products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(8)
                ->get();
            
            // Process products with photos and discounts
            $products = $this->processProductData($products);
        
        return view('frontend.pages.contact', compact('settings', 'products'));
            
        } catch (Exception $e) {
            \Log::error('Error loading contact page: ' . $e->getMessage());
            
            $settings = Settings::first();
            return view('frontend.pages.contact', [
                'settings' => $settings,
                'products' => collect()
            ]);
        }
    }

    /**
     * Display product detail page
     * 
     * @param string $slug
     * @return View
     */
    public function productDetail(string $slug): View
    {
        try {
            $product_detail = Product::getProductBySlug($slug);
            
            if (!$product_detail) {
                abort(404, 'Product not found');
        }

        // Pre-calculate product rating
            $rate = $product_detail->getReview ? ceil($product_detail->getReview->avg('rate') ?? 0) : 0;
        
        // Process product photos & other derived fields
        $this->processProductPhotos(collect([$product_detail]));
            $after_discount = $this->calculateAfterDiscount($product_detail->price, $product_detail->discount);

            // Get product variants
        $variants = $product_detail->variants()->get();
        $sizes = $variants->pluck('size')->unique()->filter()->values();
        $colors = $variants->pluck('color')->unique()->filter()->values();

            // Prepare JSON-friendly variants for frontend JS
            $productVariants = $variants->map(function($v) {
            return [
                'id' => $v->id,
                'size' => $v->size,
                'color' => $v->color,
                'price' => ($v->price !== null ? (string) $v->price : ''),
                'stock' => $v->stock,
            ];
        })->toArray();

        // Process related products with photos and discounts
            if (isset($product_detail->rel_prods) && $product_detail->rel_prods) {
                $product_detail->rel_prods = $this->processProductData($product_detail->rel_prods);
            }
            
            // Expose photo array for the view
            $photo_array = $product_detail->photo_array ?? [];

            return view('frontend.pages.product_detail', compact(
                'product_detail', 'rate', 'photo_array', 'after_discount', 
                'sizes', 'colors', 'productVariants'
            ));
            
        } catch (Exception $e) {
            \Log::error('Error loading product detail: ' . $e->getMessage(), [
                'slug' => $slug
            ]);
            abort(404, 'Product not found');
        }
    }

    /**
     * Display products in grid layout with filtering
     * 
     * @param Request $request
     * @return View
     */
    public function productGrids(Request $request): View
    {
        try {
        $products = Product::query();

            // Apply category filter
        $categoryParam = $request->query('category');
        if (!empty($categoryParam)) {
            $slug = is_array($categoryParam) ? $categoryParam : explode(',', $categoryParam);
                $cat_ids = Category::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
                $products->whereIn('cat_id', $cat_ids);
            }

            // Apply brand filter
        $brandParam = $request->query('brand');
        if (!empty($brandParam)) {
            $slugs = is_array($brandParam) ? $brandParam : explode(',', $brandParam);
            $brand_ids = Brand::select('id')->whereIn('slug', $slugs)->pluck('id')->toArray();
            $products->whereIn('brand_id', $brand_ids);
        }

            // Apply sorting
        $sortBy = $request->query('sortBy');
        if (!empty($sortBy)) {
                switch ($sortBy) {
                    case 'title':
                        $products->where('status', 'active')->orderBy('title', 'ASC');
                        break;
                    case 'price':
                        $products->orderBy('price', 'ASC');
                        break;
                    case 'newest':
                        $products->orderBy('id', 'DESC');
                        break;
                }
            }

            // Apply price filter
        $priceParam = $request->query('price');
        if (!empty($priceParam)) {
            $price = is_array($priceParam) ? $priceParam : explode('-', $priceParam);
                if (count($price) === 2 && is_numeric($price[0]) && is_numeric($price[1])) {
                    $products->whereBetween('price', [(float)$price[0], (float)$price[1]]);
                }
            }

            // Get recent products
            $recent_products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            // Process recent products
            $recent_products = $this->processProductData($recent_products);
            
            // Apply pagination
        $show = (int) $request->query('show', 9);
        $products = $products->where('status', 'active')->paginate($show > 0 ? $show : 9);

            // Get menu and filter data using Eloquent
            $menu = Category::getAllParentWithChild();
            $max = Product::max('price');
            $brands = Brand::orderBy('title', 'ASC')
                ->where('status', 'active')
                ->get();
            
            // Process products with ratings and photos
            $products_with_ratings = $this->processProductData($products);
            
            return view('frontend.pages.product-grids', compact(
                'products', 'recent_products', 'menu', 'max', 'brands', 'products_with_ratings'
            ));
            
        } catch (Exception $e) {
            \Log::error('Error loading product grids: ' . $e->getMessage());
            
            // Return minimal data on error
            return view('frontend.pages.product-grids', [
                'products' => collect(),
                'recent_products' => collect(),
                'menu' => collect(),
                'max' => 0,
                'brands' => collect(),
                'products_with_ratings' => collect()
            ]);
        }
    }
    /**
     * Display products in list layout with filtering
     * 
     * @param Request $request
     * @return View
     */
    public function productLists(Request $request): View
    {
        try {
        $products = Product::query();

            // Apply category filter
        $categoryParam = $request->query('category');
        if (!empty($categoryParam)) {
            $slug = is_array($categoryParam) ? $categoryParam : explode(',', $categoryParam);
                $cat_ids = Category::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
                $products->whereIn('cat_id', $cat_ids);
            }

            // Apply brand filter
        $brandParam = $request->query('brand');
        if (!empty($brandParam)) {
            $slugs = is_array($brandParam) ? $brandParam : explode(',', $brandParam);
            $brand_ids = Brand::select('id')->whereIn('slug', $slugs)->pluck('id')->toArray();
            $products->whereIn('brand_id', $brand_ids);
        }

            // Apply sorting
        $sortBy = $request->query('sortBy');
        if (!empty($sortBy)) {
                switch ($sortBy) {
                    case 'title':
                        $products->where('status', 'active')->orderBy('title', 'ASC');
                        break;
                    case 'price':
                        $products->orderBy('price', 'ASC');
                        break;
                    case 'newest':
                        $products->orderBy('id', 'DESC');
                        break;
                }
            }

            // Apply price filter
        $priceParam = $request->query('price');
        if (!empty($priceParam)) {
            $price = is_array($priceParam) ? $priceParam : explode('-', $priceParam);
                if (count($price) === 2 && is_numeric($price[0]) && is_numeric($price[1])) {
                    $products->whereBetween('price', [(float)$price[0], (float)$price[1]]);
                }
            }

            // Get recent products
            $recent_products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            // Process recent products
            $recent_products = $this->processProductData($recent_products);
            
            // Apply pagination
        $show = (int) $request->query('show', 6);
        $products = $products->where('status', 'active')->paginate($show > 0 ? $show : 6);

            // Get menu and filter data using Eloquent
            $menu = Category::getAllParentWithChild();
            $max = Product::max('price');
            $brands = Brand::orderBy('title', 'ASC')
                ->where('status', 'active')
                ->get();
            
            // Process products with ratings and photos
            $products_with_ratings = $this->processProductData($products);
            
            return view('frontend.pages.product-lists', compact(
                'products', 'recent_products', 'menu', 'max', 'brands', 'products_with_ratings'
            ));
            
        } catch (Exception $e) {
            \Log::error('Error loading product lists: ' . $e->getMessage());
            
            // Return minimal data on error
            return view('frontend.pages.product-lists', [
                'products' => collect(),
                'recent_products' => collect(),
                'menu' => collect(),
                'max' => 0,
                'brands' => collect(),
                'products_with_ratings' => collect()
            ]);
        }
    }
    /**
     * Handle product filtering and redirect to appropriate view
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function productFilter(Request $request): RedirectResponse
    {
        try {
            // Validate and sanitize input data
            $rules = [
                'show' => 'nullable|integer|min:1|max:50',
                'sortBy' => 'nullable|in:title,price,newest',
                'category' => 'nullable|array',
                'category.*' => 'string|max:255',
                'brand' => 'nullable|array',
                'brand.*' => 'string|max:255',
                'price_range' => 'nullable|string|max:50',
            ];
            $validatedData = \Illuminate\Support\Facades\Validator::make($request->all(), $rules)->validate();

            $data = $request->only(['show', 'sortBy', 'category', 'brand', 'price_range']);
            
            $showURL = "";
            if (!empty($data['show'])) {
                $showURL .= '&show=' . $data['show'];
            }

            $sortByURL = '';
            if (!empty($data['sortBy'])) {
                $sortByURL .= '&sortBy=' . $data['sortBy'];
            }

            $catURL = "";
            if (!empty($data['category'])) {
                foreach ($data['category'] as $category) {
                    if (empty($catURL)) {
                        $catURL .= '&category=' . urlencode($category);
                    } else {
                        $catURL .= ',' . urlencode($category);
                    }
                }
            }

            $brandURL = "";
            if (!empty($data['brand'])) {
                foreach ($data['brand'] as $brand) {
                    if (empty($brandURL)) {
                        $brandURL .= '&brand=' . urlencode($brand);
                    } else {
                        $brandURL .= ',' . urlencode($brand);
                    }
                }
            }

            $priceRangeURL = "";
            if (!empty($data['price_range'])) {
                $priceRangeURL .= '&price=' . urlencode($data['price_range']);
            }

            // Build URL parameters
            $urlParams = ltrim($catURL . $brandURL . $priceRangeURL . $showURL . $sortByURL, '&');
            
            // Determine target route based on current request
            if (request()->is('*/product-grids')) {
                $url = route('product-grids') . ($urlParams ? '?' . $urlParams : '');
            } else {
                $url = route('product-lists') . ($urlParams ? '?' . $urlParams : '');
            }

            return redirect()->to($url);
            
        } catch (Exception $e) {
            \Log::error('Error in product filter: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Filter error occurred');
        }
    }
    /**
     * Search for products and display results
     * 
     * @param Request $request
     * @return View
     */
    public function productSearch(Request $request): View
    {
        try {
            // Validate search input
            $validatedData = \Illuminate\Support\Facades\Validator::make($request->all(), ['search' => 'nullable|string|max:255'])->validate();

            $search = trim($validatedData['search'] ?? '');
            
            // Get recent products
            $recent_products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
        $products = Product::query();
            
            if ($search !== '') {
                $products->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('slug', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('summary', 'like', '%' . $search . '%')
                      ->orWhere('translations', 'like', '%' . $search . '%');
                      
                    // Only search price if it's numeric
                    if (is_numeric($search)) {
                        $q->orWhere('price', 'like', '%' . $search . '%');
                    }
                });
            }

            $products = $products->where('status', 'active')
                ->orderBy('id', 'DESC')
                ->paginate(9);
            
            // Get shared data using Eloquent
            $menu = Category::getAllParentWithChild();
            $max = Product::max('price');
            $brands = Brand::orderBy('title', 'ASC')
                ->where('status', 'active')
                ->get();

            // Process products with ratings and photos
            $products_with_ratings = $this->processProductData($products);
            $recent_products = $this->processProductData($recent_products);

            return view('frontend.pages.product-grids', compact(
                'products', 'recent_products', 'menu', 'max', 'brands', 'products_with_ratings'
            ));
            
        } catch (Exception $e) {
            \Log::error('Error in product search: ' . $e->getMessage());
            
            // Return minimal data on error
            return view('frontend.pages.product-grids', [
                'products' => collect(),
                'recent_products' => collect(),
                'menu' => collect(),
                'max' => 0,
                'brands' => collect(),
                'products_with_ratings' => collect()
            ]);
        }
    }

    /**
     * Display products by brand
     * 
     * @param Request $request
     * @return View
     */
    public function productBrand(Request $request, string $slug = null): View
    {
        try {
            // Obtain slug from route parameter first, then from request input (query/body)
            $slugParam = $slug ?? $request->route('slug') ?? $request->input('slug');
            $validated = \Illuminate\Support\Facades\Validator::make(
                ['slug' => $slugParam],
                ['slug' => 'required|string|max:255']
            )->validate();

            // Retrieve brand with products
            $brand = Brand::getProductByBrand($validated['slug']);
            if (!$brand || !$brand->products) {
                abort(404, 'Brand not found');
            }

            $products = $brand->products; // Collection of products to display

            // Recent products
            $recent_products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();

            // Shared data
            $menu = Category::getAllParentWithChild();
            $max = Product::max('price');
            $brands = Brand::orderBy('title', 'ASC')
                ->where('status', 'active')
                ->get();

            // Process product data (ratings, photos, discount)
            $products_with_ratings = $this->processProductData($products);
            $recent_products = $this->processProductData($recent_products);

            // Always show in grids when coming from brand filter (can be adjusted if needed)
            return view('frontend.pages.product-grids', compact(
                'products', 'recent_products', 'menu', 'max', 'brands', 'products_with_ratings'
            ));
        } catch (Exception $e) {
            \Log::error('Error loading brand products: ' . $e->getMessage(), [
                'slug' => $slug ?? $request->route('slug') ?? $request->input('slug')
            ]);
            abort(404, 'Brand not found');
        }
    }
    /**
     * Display products by category
     * 
     * @param Request $request
     * @return View
     */
    public function productCat(Request $request, string $slug = null): View
    {
        try {
            // Read slug from route parameter first, then from request input
            $slugParam = $slug ?? $request->route('slug') ?? $request->input('slug');
            $validatedData = \Illuminate\Support\Facades\Validator::make(['slug' => $slugParam], ['slug' => 'required|string|max:255'])->validate();

            $category = Category::getProductByCat($validatedData['slug']);
            
        if (!$category || !$category->products) {
                abort(404, 'Category not found');
            }
            
            $products = $category->products;
            
            // Get recent products
            $recent_products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            // Get shared view data using Eloquent
            $menu = Category::getAllParentWithChild();
            $max = Product::max('price');
            $brands = Brand::orderBy('title', 'ASC')
                ->where('status', 'active')
                ->get();

            // Process products with ratings and photos
            $products_with_ratings = $this->processProductData($products);
            $recent_products = $this->processProductData($recent_products);

            // Determine view based on current request
            $viewName = request()->is('*/product-grids') ? 'frontend.pages.product-grids' : 'frontend.pages.product-lists';
            
            return view($viewName, compact(
                'products', 'recent_products', 'menu', 'max', 'brands', 'products_with_ratings'
            ));
            
        } catch (Exception $e) {
            \Log::error('Error loading category products: ' . $e->getMessage(), [
                'slug' => $request->input('slug')
            ]);
            abort(404, 'Category not found');
        }
    }
    /**
     * Display products by subcategory
     * 
     * @param Request $request
     * @return View
     */
    public function productSubCat(Request $request, string $slug = null, string $sub_slug = null): View
    {
        try {
            // Try to obtain sub_slug from route parameters first, then from request input
            $subSlug = $sub_slug ?? $request->route('sub_slug') ?? $request->input('sub_slug');
            $validated = \Illuminate\Support\Facades\Validator::make(['sub_slug' => $subSlug], ['sub_slug' => 'required|string|max:255'])->validate();

            $category = Category::getProductBySubCat($validated['sub_slug']);
            
        if (!$category || !$category->sub_products) {
                abort(404, 'Subcategory not found');
            }
            
            $products = $category->sub_products;
            
            // Get recent products
            $recent_products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            // Get shared view data using Eloquent
            $menu = Category::getAllParentWithChild();
            $max = Product::max('price');
            $brands = Brand::orderBy('title', 'ASC')
                ->where('status', 'active')
                ->get();

            // Process products with ratings and photos
            $products_with_ratings = $this->processProductData($products);
            $recent_products = $this->processProductData($recent_products);

            // Determine view based on current request
            $viewName = request()->is('*/product-grids') ? 'frontend.pages.product-grids' : 'frontend.pages.product-lists';
            
            return view($viewName, compact(
                'products', 'recent_products', 'menu', 'max', 'brands', 'products_with_ratings'
            ));
            
        } catch (Exception $e) {
            \Log::error('Error loading subcategory products: ' . $e->getMessage(), [
                'sub_slug' => $request->input('sub_slug')
            ]);
            abort(404, 'Subcategory not found');
        }
    }

    /**
     * Display blog posts with filtering
     * 
     * @param Request $request
     * @return View
     */
    public function blog(Request $request): View
    {
        try {
            $rules = [
                'category' => 'nullable|array',
                'category.*' => 'string|max:255',
                'tag' => 'nullable|array',
                'tag.*' => 'string|max:255',
                'show' => 'nullable|integer|min:1|max:50'
            ];
            $validatedData = \Illuminate\Support\Facades\Validator::make($request->all(), $rules)->validate();

        $post = Post::query();
        
        // Handle filter categories
        $filter_cats = [];
        $filter_tags = [];
            
            $categoryParam = $validatedData['category'] ?? $request->query('category');
        if (!empty($categoryParam)) {
            $slug = is_array($categoryParam) ? $categoryParam : explode(',', $categoryParam);
            $filter_cats = $slug;
            $cat_ids = PostCategory::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
            $post->whereIn('post_cat_id', $cat_ids);
        }

            $tagParam = $validatedData['tag'] ?? $request->query('tag');
        if (!empty($tagParam)) {
            $slug = is_array($tagParam) ? $tagParam : explode(',', $tagParam);
            $filter_tags = $slug;
            $tag_ids = PostTag::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
            $post->whereIn('post_tag_id', $tag_ids);
        }

            $show = (int) ($validatedData['show'] ?? $request->query('show', 9));
            $posts = $post->where('status', 'active')
                ->orderBy('id', 'DESC')
                ->paginate($show > 0 ? $show : 9);
            
            // Get recent posts
            $recent_posts = Post::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
        return view('frontend.pages.blog', compact('posts', 'recent_posts', 'filter_cats', 'filter_tags'));
            
        } catch (Exception $e) {
            \Log::error('Error loading blog: ' . $e->getMessage());
            
            // Return minimal data on error
            return view('frontend.pages.blog', [
                'posts' => collect(),
                'recent_posts' => collect(),
                'filter_cats' => [],
                'filter_tags' => []
            ]);
        }
    }

    /**
     * Display blog post detail
     * 
     * @param string $slug
     * @return View
     */
    public function blogDetail(string $slug): View
    {
        try {
            $post = Post::getPostBySlug($slug);
            
        if (!$post) {
                abort(404, 'Post not found');
            }
            
            // Get recent posts
            $recent_posts = Post::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            // Process tags
            $tags = !empty($post->tags) ? explode(',', $post->tags) : [];
            
        return view('frontend.pages.blog-detail', compact('post', 'recent_posts', 'tags'));
            
        } catch (Exception $e) {
            \Log::error('Error loading blog detail: ' . $e->getMessage(), [
                'slug' => $slug
            ]);
            abort(404, 'Post not found');
        }
    }

    /**
     * Search blog posts
     * 
     * @param Request $request
     * @return View
     */
    public function blogSearch(Request $request): View
    {
        try {
            $validatedData = \Illuminate\Support\Facades\Validator::make($request->all(), ['search' => 'required|string|max:255'])->validate();

            $search = trim($validatedData['search']);
            
            // Get recent posts
            $recent_posts = Post::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            // Search posts
            $posts = Post::where('status', 'active')
                ->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('quote', 'like', '%' . $search . '%')
                      ->orWhere('summary', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('slug', 'like', '%' . $search . '%');
                })
                ->orderBy('id', 'DESC')
            ->paginate(8);
            
            return view('frontend.pages.blog', compact('posts', 'recent_posts'));
            
        } catch (Exception $e) {
            \Log::error('Error in blog search: ' . $e->getMessage());
            
            // Return minimal data on error
            return view('frontend.pages.blog', [
                'posts' => collect(),
                'recent_posts' => collect()
            ]);
        }
    }

    /**
     * Handle blog filtering and redirect
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function blogFilter(Request $request): RedirectResponse
    {
        try {
            $validatedData = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'category' => 'nullable|array',
                'category.*' => 'string|max:255',
                'tag' => 'nullable|array',
                'tag.*' => 'string|max:255'
            ])->validate();

            $data = $request->only(['category', 'tag']);
            
            $catURL = "";
            if (!empty($data['category'])) {
                foreach ($data['category'] as $category) {
                    if (empty($catURL)) {
                        $catURL .= '&category=' . urlencode($category);
                    } else {
                        $catURL .= ',' . urlencode($category);
                    }
                }
            }

            $tagURL = "";
            if (!empty($data['tag'])) {
                foreach ($data['tag'] as $tag) {
                    if (empty($tagURL)) {
                        $tagURL .= '&tag=' . urlencode($tag);
                    } else {
                        $tagURL .= ',' . urlencode($tag);
                    }
                }
            }

            $urlParams = ltrim($catURL . $tagURL, '&');
            $url = route('blog') . ($urlParams ? '?' . $urlParams : '');
            
            return redirect()->to($url);
            
        } catch (Exception $e) {
            \Log::error('Error in blog filter: ' . $e->getMessage());
            return redirect()->route('blog')->with('error', 'Filter error occurred');
        }
    }

    /**
     * Display blog posts by category
     * 
     * @param Request $request
     * @return View
     */
    public function blogByCategory(Request $request, $slug): View
    {
        try {
            if (empty($slug)) {
                abort(404, 'Category slug is required');
            }

            $post = PostCategory::getBlogByCategory($slug);
            
            if (!$post || !isset($post->posts)) {
                abort(404, 'Category not found');
            }
            
            // Get recent posts
            $recent_posts = Post::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            $posts = $post->posts;
            
            return view('frontend.pages.blog', compact('posts', 'recent_posts'));
            
        } catch (Exception $e) {
            \Log::error('Error loading blog by category: ' . $e->getMessage(), [
                'slug' => $slug
            ]);
            abort(404, 'Category not found');
        }
    }

    /**
     * Display blog posts by tag
     * 
     * @param Request $request
     * @return View
     */
    public function blogByTag(Request $request, $slug): View
    {
        try {
            if (empty($slug)) {
                abort(404, 'Tag slug is required');
            }

            $post = Post::getBlogByTag($slug);
            
            if (!$post) {
                abort(404, 'Tag not found');
            }
            
            // Get recent posts
            $recent_posts = Post::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
            
            $posts = $post;
            
            return view('frontend.pages.blog', compact('posts', 'recent_posts'));
            
        } catch (Exception $e) {
            \Log::error('Error loading blog by tag: ' . $e->getMessage(), [
                'slug' => $slug
            ]);
            abort(404, 'Tag not found');
        }
    }

    /**
     * Display login form
     * 
     * @return View
     */
    public function login(): View
    {
        try {
            $settings = Settings::first();
        return view('frontend.pages.login', compact('settings'));
        } catch (Exception $e) {
            \Log::error('Error loading login page: ' . $e->getMessage());
            return view('frontend.pages.login', ['settings' => null]);
        }
    }

    /**
     * Handle login submission
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function loginSubmit(LoginRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            if (Auth::attempt([
                'email' => $validatedData['email'], 
                'password' => $validatedData['password'],
                'status' => 'active'
            ])) {
                $user = Auth::user();
                Session::put('user', $validatedData['email']);
                request()->session()->flash('success', 'Successfully logged in');
                
                // Redirect based on user role
                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role === 'user') {
                    return redirect()->route('user');
                }
                
                return redirect()->route('home');
            } else {
                request()->session()->flash('error', 'Invalid email and password. Please try again!');
                return redirect()->back();
            }
            
        } catch (Exception $e) {
            \Log::error('Error in login: ' . $e->getMessage());
            request()->session()->flash('error', 'Login error occurred');
            return redirect()->back();
        }
    }

    /**
     * Handle user logout
     * 
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        try {
        Session::forget('user');
        Auth::logout();
            request()->session()->flash('success', 'Logout successfully');
            return back();
        } catch (Exception $e) {
            \Log::error('Error in logout: ' . $e->getMessage());
        return back();
        }
    }

    /**
     * Display registration form
     * 
     * @return View
     */
    public function register(): View
    {
        try {
            $settings = Settings::first();
        return view('frontend.pages.register', compact('settings'));
        } catch (Exception $e) {
            \Log::error('Error loading register page: ' . $e->getMessage());
            return view('frontend.pages.register', ['settings' => null]);
        }
    }

    /**
     * Handle registration submission
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function registerSubmit(RegisterRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $user = $this->create($validatedData);
            
            if ($user) {
                Session::put('user', $validatedData['email']);
                request()->session()->flash('success', 'Successfully registered');
            return redirect()->route('home');
            } else {
                request()->session()->flash('error', 'Registration failed. Please try again!');
                return back();
            }
            
        } catch (Exception $e) {
            \Log::error('Error in registration: ' . $e->getMessage());
            request()->session()->flash('error', 'Registration error occurred');
            return back();
        }
    }

    /**
     * Create new user
     * 
     * @param array $data
     * @return User|null
     */
    private function create(array $data): ?User
    {
        try {
        return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active'
            ]);
        } catch (Exception $e) {
            \Log::error('Error creating user: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Display password reset form
     * 
     * @return View
     */
    public function showResetForm(): View
    {
        try {
            return view('auth.passwords.old-reset');
        } catch (Exception $e) {
            \Log::error('Error loading reset form: ' . $e->getMessage());
            abort(404, 'Reset form not found');
        }
    }

    /**
     * Handle newsletter subscription
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function subscribe(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'email' => 'required|email|max:255'
            ]);

            $email = $request->email;
            $name = $request->name;

            if (!Newsletter::isSubscribed($email)) {
                Newsletter::subscribe($email, $name);
                
                request()->session()->flash('success', __('newsletter.subscribed_successfully'));
                return redirect()->back();
            } else {
                request()->session()->flash('error', __('newsletter.already_subscribed'));
                return redirect()->back();
            }
            
        } catch (\Exception $e) {
            \Log::error('Newsletter subscribe failed: ' . $e->getMessage());
            return redirect()->back()->with('error', __('newsletter.subscription_failed'));
        }
    }

    /**
     * Handle newsletter unsubscription
     * 
     * @param string $token
     * @return RedirectResponse
     */
    public function unsubscribe($token): RedirectResponse
    {
        try {
            if (Newsletter::unsubscribeByToken($token)) {
                request()->session()->flash('success', __('newsletter.unsubscribed_successfully'));
            } else {
                request()->session()->flash('error', __('newsletter.invalid_unsubscribe_token'));
            }
            
            return redirect()->route('home');
        } catch (\Exception $e) {
            \Log::error('Newsletter unsubscribe failed: ' . $e->getMessage());
            return redirect()->route('home')->with('error', __('newsletter.unsubscription_failed'));
        }
    }
    
}