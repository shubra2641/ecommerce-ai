<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Language;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductVariant;
use Exception;

/**
 * ProductController handles product management operations
 * 
 * This controller manages product creation, editing, deletion, and
 * multi-language translation support with secure validation and proper error handling.
 */
class ProductController extends Controller
{
    public function __construct()
    {
        $this->shareCommonViewData();
    }
    /**
     * Display a listing of products
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $products = Product::with(['cat_info', 'sub_cat_info', 'brand'])->paginate(10);

            // Process products with photo arrays and calculations
            $products->getCollection()->transform(function (Product $product) {
                $product->photo_array = $product->photo !== '' ? explode(',', $product->photo) : [];
                $product->after_discount = ($product->price - (($product->price * ($product->discount ?? 0)) / 100));

                return $product;
            });

            $subCategories = Category::select('id', 'title')->get();
            $brands = Brand::select('id', 'title')->get();

            return view('backend.product.index', [
                'products' => $products,
                'sub_categories' => $subCategories,
                'brands' => $brands,
            ]);
            
        } catch (Exception $e) {
            Log::error('Error loading products: ' . $e->getMessage());
            return view('backend.product.index', [
                'products' => collect(),
                'sub_categories' => collect(),
                'brands' => collect(),
            ]);
        }
    }

    /**
     * Show the form for creating a new product
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            $brands = Brand::all();
            $categories = Category::where('is_parent', 1)->get();

            return view('backend.product.create', compact('categories', 'brands'));
            
        } catch (Exception $e) {
            Log::error('Error loading create product form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created product
     * 
     * @param ProductRequest $request
     * @return RedirectResponse
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Generate unique slug
            $validatedData['slug'] = $this->generateUniqueSlug($validatedData['title']);
            $validatedData['is_featured'] = $request->input('is_featured', 0);
            $validatedData['size'] = $request->has('size') ? implode(',', $request->input('size')) : '';

            // Process translations
            $validatedData['translations'] = $this->processTranslations($request, $validatedData);

            DB::beginTransaction();

            try {
                $product = Product::create($validatedData);

                // Handle variants if provided
                if ($product && $request->has('variants')) {
                    $this->processVariants($product, $request);
                }

                DB::commit();

                return redirect()->route('product.index')->with('success', 'Product successfully added');
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Error storing product: ' . $e->getMessage(), [
                'request_data' => $request->only(['title', 'price', 'cat_id'])
            ]);
            return redirect()->route('product.index')->with('error', 'An error occurred while saving the product.');
        }
    }

    /**
     * Generate unique slug for product
     * 
     * @param string $title
     * @return string
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = function_exists('generateUniqueSlug') 
            ? generateUniqueSlug($title, Product::class) 
            : Str::slug($title);
            
        if (!$slug) {
            $slug = Str::slug($title);
        }
        
        $originalSlug = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Process translations for product
     * 
     * @param ProductRequest $request
     * @param array $validatedData
     * @return array
     */
    private function processTranslations(ProductRequest $request, array $validatedData): array
    {
        $translations = $request->input('translations', []);
        $default = Language::getDefault();
        $defaultCode = $default ? $default->code : app()->getLocale();
        
        if (!isset($translations[$defaultCode])) {
            $translations[$defaultCode] = [];
        }
        
        $translations[$defaultCode]['title'] = $validatedData['title'];
        $translations[$defaultCode]['summary'] = $validatedData['summary'];
        $translations[$defaultCode]['description'] = $validatedData['description'] ?? '';

        return $translations;
    }

    /**
     * Process product variants
     * 
     * @param Product $product
     * @param ProductRequest $request
     * @return void
     */
    private function processVariants(Product $product, ProductRequest $request): void
    {
        $variants = $request->input('variants');
        $normalizedVariants = $this->normalizeVariants($variants);

        foreach ($normalizedVariants as $variant) {
            $size = isset($variant['size']) ? trim(strip_tags((string) $variant['size'])) : null;
            $color = isset($variant['color']) ? trim(strip_tags((string) $variant['color'])) : null;
            
            if (empty($size) && empty($color)) {
                continue;
            }

            $variantPrice = $this->calculateVariantPrice($variant, $product->price);
            $variantStock = isset($variant['stock']) ? (int) $variant['stock'] : 0;
            $sku = isset($variant['sku']) ? trim(strip_tags((string) $variant['sku'])) : null;

            ProductVariant::create([
                'product_id' => $product->id,
                'size' => $size,
                'color' => $color,
                'price' => $variantPrice,
                'stock' => $variantStock,
                'sku' => $sku,
            ]);
        }
    }

    /**
     * Normalize variants input
     * 
     * @param array $variants
     * @return array
     */
    private function normalizeVariants(array $variants): array
    {
        if (isset($variants['size']) && is_array($variants['size'])) {
            $sizes = $variants['size'];
            $colors = $variants['color'] ?? [];
            $prices = $variants['price'] ?? [];
            $stocks = $variants['stock'] ?? [];
            $skus = $variants['sku'] ?? [];
            $ids = $variants['id'] ?? [];
            
            $normalized = [];
            $max = max(count($sizes), count($colors), count($prices), count($stocks), count($skus), count($ids));
            
            for ($i = 0; $i < $max; $i++) {
                $normalized[] = [
                    'id' => $ids[$i] ?? null,
                    'size' => $sizes[$i] ?? null,
                    'color' => $colors[$i] ?? null,
                    'price' => $prices[$i] ?? null,
                    'stock' => $stocks[$i] ?? null,
                    'sku' => $skus[$i] ?? null,
                ];
            }

            return $normalized;
        }

        return array_values($variants);
    }

    /**
     * Calculate variant price
     * 
     * @param array $variant
     * @param float $defaultPrice
     * @return float
     */
    private function calculateVariantPrice(array $variant, float $defaultPrice): float
    {
        if (isset($variant['price']) && $variant['price'] !== '') {
            $price = filter_var($variant['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            return is_numeric($price) ? (float) $price : $defaultPrice;
        }

        return $defaultPrice;
    }

    /**
     * Display the specified product
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $product = Product::findOrFail($id);
            $translations = $product->translations ?? [];
            $variants = $product->variants;
            $photoArray = $product->photo !== '' ? explode(',', $product->photo) : [];
            
            return view('backend.product.show', compact('product', 'translations', 'variants', 'photoArray'));
            
        } catch (Exception $e) {
            Log::error('Error loading product details: ' . $e->getMessage(), [
                'product_id' => $id
            ]);
            abort(404, 'Product not found');
        }
    }

    /**
     * Show the form for editing the specified product
     * 
     * @param Product $product
     * @return View
     */
    public function edit(Product $product): View
    {
        try {
            $brands = Brand::all();
            $categories = Category::where('is_parent', 1)->get();

            $items = Product::where('id', $product->id)->get();

            // Process items with photo arrays
            $items = $items->map(function ($item) {
                $item->photo_array = $item->photo !== '' ? explode(',', $item->photo) : [];
                return $item;
            });

            // Prepare initial variants JSON for the edit form JS
            $initialVariants = $product->variants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'size' => $v->size,
                    'color' => $v->color,
                    'price' => ($v->price !== null ? (string) $v->price : ''),
                    'stock' => $v->stock,
                    'sku' => $v->sku,
                ];
            })->toArray();

            // Prepare translations map for tabbed language inputs
            $translations = $product->translations ?? [];

            $subCatInfo = Category::select('title')->where('id', $product->child_cat_id)->get();

            return view('backend.product.edit', compact('product', 'brands', 'categories', 'items', 'subCatInfo', 'initialVariants', 'translations'));
            
        } catch (Exception $e) {
            Log::error('Error loading edit product form: ' . $e->getMessage(), [
                'product_id' => $product->id
            ]);
            abort(404, 'Product not found');
        }
    }

    /**
     * Update the specified product
     * 
     * @param ProductRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $validatedData['is_featured'] = $request->input('is_featured', 0);
            $validatedData['size'] = $request->has('size') ? implode(',', $request->input('size')) : '';

            // Update slug if title changed compared to original DB value
            $originalTitle = $product->getOriginal('title');
            if ($request->title !== $originalTitle) {
                $validatedData['slug'] = $this->generateUniqueSlug($request->title);
            }

            // Merge translations
            $validatedData['translations'] = $this->mergeTranslations($product, $request, $validatedData);

            DB::beginTransaction();

            try {
                $status = $product->update($validatedData);

                // Sync variants on update
                if ($status) {
                    $this->syncVariants($product, $request);
                }

                DB::commit();

                $message = $status ? 'Product successfully updated' : 'Please try again!';
                return redirect()->route('product.index')->with($status ? 'success' : 'error', $message);
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'request_data' => $request->only(['title', 'price', 'cat_id'])
            ]);
            return redirect()->route('product.index')->with('error', 'An error occurred while updating the product.');
        }
    }

    /**
     * Merge translations for product update
     * 
     * @param Product $product
     * @param ProductRequest $request
     * @param array $validatedData
     * @return array
     */
    private function mergeTranslations(Product $product, ProductRequest $request, array $validatedData): array
    {
        $existing = $product->translations ?? [];
        $incoming = $request->input('translations', []);
        $merged = array_replace_recursive($existing, $incoming);
        
        $default = Language::getDefault();
        $defaultCode = $default ? $default->code : app()->getLocale();
        
        if (!isset($merged[$defaultCode])) {
            $merged[$defaultCode] = [];
        }
        
        $merged[$defaultCode]['title'] = $validatedData['title'];
        $merged[$defaultCode]['summary'] = $validatedData['summary'];
        $merged[$defaultCode]['description'] = $validatedData['description'] ?? '';

        return $merged;
    }

    /**
     * Sync product variants
     * 
     * @param Product $product
     * @param ProductRequest $request
     * @return void
     */
    private function syncVariants(Product $product, ProductRequest $request): void
    {
        $existingIds = $product->variants()->pluck('id')->toArray();

        if ($request->has('variants')) {
            $variants = $request->input('variants');
            $normalizedVariants = $this->normalizeVariants($variants);

            if (empty($normalizedVariants)) {
                if (!empty($existingIds)) {
                    ProductVariant::where('product_id', $product->id)->delete();
                }
            } else {
                $incomingIds = [];
                
                foreach ($normalizedVariants as $variant) {
                    $vid = $variant['id'] ?? null;
                    
                    if (!empty($vid) && is_numeric($vid)) {
                        $existingVariant = ProductVariant::find((int) $vid);
                        if ($existingVariant) {
                            $this->updateVariant($existingVariant, $variant, $product->price);
                            $incomingIds[] = (int) $existingVariant->id;
                            continue;
                        }
                    }

                    $newVariant = $this->createVariant($product, $variant);
                    if ($newVariant) {
                        $incomingIds[] = (int) $newVariant->id;
                    }
                }

                $toDelete = array_diff($existingIds, $incomingIds ?: []);
                if (!empty($toDelete)) {
                    ProductVariant::where('product_id', $product->id)->whereIn('id', $toDelete)->delete();
                }
            }
        } else {
            if (!empty($existingIds)) {
                ProductVariant::where('product_id', $product->id)->delete();
            }
        }
    }

    /**
     * Update existing variant
     * 
     * @param ProductVariant $variant
     * @param array $variantData
     * @param float $defaultPrice
     * @return void
     */
    private function updateVariant(ProductVariant $variant, array $variantData, float $defaultPrice): void
    {
        $variantPrice = $this->calculateVariantPrice($variantData, $defaultPrice);
        $variantStock = isset($variantData['stock']) ? (int) $variantData['stock'] : 0;

        $variant->update([
            'size' => $variantData['size'] ?? null,
            'color' => $variantData['color'] ?? null,
            'price' => $variantPrice,
            'stock' => $variantStock,
            'sku' => $variantData['sku'] ?? null,
        ]);
    }

    /**
     * Create new variant
     * 
     * @param Product $product
     * @param array $variantData
     * @return ProductVariant|null
     */
    private function createVariant(Product $product, array $variantData): ?ProductVariant
    {
        $size = isset($variantData['size']) ? trim(strip_tags((string) $variantData['size'])) : null;
        $color = isset($variantData['color']) ? trim(strip_tags((string) $variantData['color'])) : null;
        
        if (empty($size) && empty($color)) {
            return null;
        }

        $variantPrice = $this->calculateVariantPrice($variantData, $product->price);
        $variantStock = isset($variantData['stock']) ? (int) $variantData['stock'] : 0;
        $sku = isset($variantData['sku']) ? trim(strip_tags((string) $variantData['sku'])) : null;

        return ProductVariant::create([
            'product_id' => $product->id,
            'size' => $size,
            'color' => $color,
            'price' => $variantPrice,
            'stock' => $variantStock,
            'sku' => $sku,
        ]);
    }

    /**
     * Remove the specified product from storage
     * 
     * @param Product $product
     * @return RedirectResponse
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();

            try {
                // Delete associated variants first
                $product->variants()->delete();
                
                $status = $product->delete();

                DB::commit();

                $message = $status ? 'Product successfully deleted' : 'Error while deleting product';
                return redirect()->route('product.index')->with($status ? 'success' : 'error', $message);
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage(), [
                'product_id' => $product->id
            ]);
            return redirect()->route('product.index')->with('error', 'An error occurred while deleting the product.');
        }
    }
}