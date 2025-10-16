<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use \UniSharp\LaravelFilemanager\Lfm;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Controllers
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminPaymentGatewayController;
use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\FontController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PostTagController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CompareController;
use App\Http\Controllers\Frontend\CouponController as FrontendCouponController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\MessageController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\PayPalController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\PostCommentController;
use App\Http\Controllers\User\ProductReviewController;

/*
|--------------------------------------------------------------------------
| System Routes
|--------------------------------------------------------------------------
|
| Routes for system maintenance and utilities
|
*/

// Cache Management
Route::get('cache-clear', function () {
    Artisan::call('optimize:clear');
    request()->session()->flash('success', 'Successfully cache cleared.');
    return redirect()->back();
})->name('cache.clear');

// Storage Link
Route::get('storage-link', [AdminController::class, 'storageLink'])->name('storage.link');

/*
|--------------------------------------------------------------------------
| Asset Routes
|--------------------------------------------------------------------------
|
| Routes for serving static assets
|
*/

// Static Assets Routes (specific routes first)
Route::get('css/frontend-fonts.css', [FontController::class, 'frontendCss'])->name('fonts.frontend.css');
Route::get('css/backend-fonts.css', [FontController::class, 'backendCss'])->name('fonts.backend.css');

// Static Assets Routes (general routes after specific ones)
Route::get('css/{file}', [AssetController::class, 'css'])->where('file', '.*');
Route::get('js/{file}', [AssetController::class, 'js'])->where('file', '.*');
Route::get('images/{file}', [AssetController::class, 'images'])->where('file', '.*');
Route::get('favicon.png', [AssetController::class, 'favicon']);

/*
|--------------------------------------------------------------------------
| Language Routes
|--------------------------------------------------------------------------
|
| Routes for language switching
|
*/

Route::get('switch-language/{code}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Routes for user authentication and registration
|
*/

// Laravel Auth Routes
Auth::routes(['register' => false]);

// Frontend Authentication
Route::prefix('user')->group(function () {
    Route::get('/login', [FrontendController::class, 'login'])->name('login.form');
    Route::post('/login', [FrontendController::class, 'loginSubmit'])->name('login.submit');
    Route::get('/logout', [FrontendController::class, 'logout'])->name('user.logout');
    Route::get('/register', [FrontendController::class, 'register'])->name('register.form');
    Route::post('/register', [FrontendController::class, 'registerSubmit'])->name('register.submit');
});

// Password Reset Routes
Route::prefix('password')->group(function () {
    Route::get('/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Social Authentication
Route::prefix('login')->group(function () {
    Route::get('/{provider}/', [LoginController::class, 'redirect'])->name('login.redirect');
    Route::get('/{provider}/callback/', [LoginController::class, 'Callback'])->name('login.callback');
});

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
|
| Routes for the public frontend
|
*/

// Home Routes
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/home', [FrontendController::class, 'index']);

// Static Pages
Route::get('/about-us', [FrontendController::class, 'aboutUs'])->name('about-us');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
Route::post('/contact/message', [MessageController::class, 'store'])->name('contact.store');

// Product Routes
Route::prefix('product')->group(function () {
    Route::get('/detail/{slug}', [FrontendController::class, 'productDetail'])->name('product-detail');
    Route::post('/search', [FrontendController::class, 'productSearch'])->name('product.search');
    Route::get('/cat/{slug}', [FrontendController::class, 'productCat'])->name('product-cat');
    Route::get('/sub-cat/{slug}/{sub_slug}', [FrontendController::class, 'productSubCat'])->name('product-sub-cat');
    Route::get('/brand/{slug}', [FrontendController::class, 'productBrand'])->name('product-brand');
    Route::get('/grids', [FrontendController::class, 'productGrids'])->name('product-grids');
    Route::get('/lists', [FrontendController::class, 'productLists'])->name('product-lists');
    Route::match(['get', 'post'], '/filter', [FrontendController::class, 'productFilter'])->name('shop.filter');
});

// Cart Routes
Route::prefix('cart')->group(function () {
    Route::get('/add/{slug}', [CartController::class, 'addToCart'])->name('add-to-cart')->middleware('user');
    Route::post('/add', [CartController::class, 'singleAddToCart'])->name('single-add-to-cart')->middleware('user');
    Route::get('/delete/{id}', [CartController::class, 'cartDelete'])->name('cart-delete');
    Route::post('/update', [CartController::class, 'cartUpdate'])->name('cart.update');
    Route::get('/', [CartController::class, 'index'])->name('cart');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout')->middleware('user');
    Route::post('/order', [OrderController::class, 'store'])->name('cart.order');
});

// Wishlist Routes
Route::prefix('wishlist')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('wishlist');
    Route::get('/{slug}', [WishlistController::class, 'wishlist'])->name('add-to-wishlist')->middleware('user');
    Route::get('/delete/{id}', [WishlistController::class, 'wishlistDelete'])->name('wishlist-delete');
});

// Compare Routes
Route::prefix('compare')->group(function () {
    Route::get('/', [CompareController::class, 'index'])->name('compare.index');
    Route::get('/add/{slug}', [CompareController::class, 'add'])->name('compare.add');
    Route::get('/remove/{id}', [CompareController::class, 'remove'])->name('compare.remove');
    Route::get('/clear', [CompareController::class, 'clear'])->name('compare.clear');
});

// Order Routes
Route::prefix('order')->group(function () {
    Route::get('/pdf/{id}', [OrderController::class, 'pdf'])->name('order.pdf');
    Route::get('/track', [OrderController::class, 'orderTrack'])->name('order.track');
    Route::post('/track/order', [OrderController::class, 'productTrackOrder'])->name('product.track.order');
});

// Product Track Route (outside order prefix)
Route::get('/product/track', [OrderController::class, 'orderTrack'])->name('product.track');

// Blog Routes
Route::prefix('blog')->group(function () {
    Route::get('/', [FrontendController::class, 'blog'])->name('blog');
    Route::get('/detail/{slug}', [FrontendController::class, 'blogDetail'])->name('blog.detail');
    Route::get('/search', [FrontendController::class, 'blogSearch'])->name('blog.search');
    Route::post('/filter', [FrontendController::class, 'blogFilter'])->name('blog.filter');
    Route::get('/cat/{slug}', [FrontendController::class, 'blogByCategory'])->name('blog.category');
    Route::get('/tag/{slug}', [FrontendController::class, 'blogByTag'])->name('blog.tag');
});

// Newsletter
Route::post('/subscribe', [FrontendController::class, 'subscribe'])->name('subscribe');
Route::get('/newsletter/unsubscribe/{token}', [FrontendController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Product Review Routes
Route::resource('/review', ProductReviewController::class);
Route::post('product/{slug}/review', [ProductReviewController::class, 'store'])->name('product.review.store');

// Post Comment Routes
Route::post('post/{slug}/comment', [PostCommentController::class, 'store'])->name('post-comment.store');
Route::resource('/comment', PostCommentController::class);

// Coupon Routes
Route::post('/coupon-store', [FrontendCouponController::class, 'couponStore'])->name('coupon-store');

// Payment Routes
Route::prefix('payment')->middleware('auth')->group(function () {
    Route::get('/process', [App\Http\Controllers\Frontend\PaymentController::class, 'process'])->name('payment.process');
    Route::get('/success', [App\Http\Controllers\Frontend\PaymentController::class, 'success'])->name('payment.success');
    Route::get('/cancel', [App\Http\Controllers\Frontend\PaymentController::class, 'cancel'])->name('payment.cancel');
});

// Payment Webhooks (no auth required)
Route::prefix('webhook')->group(function () {
    Route::post('/{gateway}', [App\Http\Controllers\Frontend\PaymentController::class, 'webhook'])->name('payment.webhook');
});

// Income Chart
Route::get('/income', [OrderController::class, 'incomeChart'])->name('product.order.income');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes for admin panel (protected by auth and admin middleware)
|
*/

Route::group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function () {
    
    // Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('admin');
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // File Manager
    Route::get('/file-manager', function () {
        return view('backend.layouts.file-manager');
    })->name('file-manager');
    
    // User Management
    Route::resource('users', UsersController::class);
    
    // Banner Management
    Route::resource('banner', BannerController::class);
    
    // Brand Management
    Route::resource('brand', BrandController::class);
    
    // Category Management
    Route::resource('/category', CategoryController::class);
    Route::post('/category/{id}/child', [CategoryController::class, 'getChildByParent']);
    
    // Product Management
    Route::resource('/product', ProductController::class);
    
    // Post Management
    Route::resource('/post-category', PostCategoryController::class);
    Route::resource('/post-tag', PostTagController::class);
    Route::resource('/post', PostController::class);
    
    // Message Management
    Route::resource('/message', AdminMessageController::class);
    Route::get('/message/five', [MessageController::class, 'messageFive'])->name('messages.five');
    Route::get('/message/list', [MessageController::class, 'message'])->name('message.list');
    
    // Order Management
    Route::resource('/order', AdminOrderController::class);
    
    // Shipping Management
    Route::resource('/shipping', ShippingController::class);
    
    // Coupon Management
    Route::resource('/coupon', CouponController::class);
    
    // Settings
    Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('setting/update', [AdminController::class, 'settingsUpdate'])->name('settings.update');
    
    // AI Generation
    Route::post('ai/generate', [AiController::class, 'generate'])->name('admin.ai.generate');
    
    // Language Management
    Route::resource('language', LanguageController::class);
    Route::get('language/{id}/set-default', [LanguageController::class, 'setDefault'])->name('language.set-default');
    Route::get('language/{id}/toggle-status', [LanguageController::class, 'toggleStatus'])->name('language.toggle-status');
    Route::get('language/{id}/file/{filename}/edit', [LanguageController::class, 'editFile'])->name('language.edit-file');
    Route::post('language/{id}/file/{filename}/update', [LanguageController::class, 'updateFile'])->name('language.update-file');
    Route::get('language/{id}/file/{filename}/create', [LanguageController::class, 'createFile'])->name('language.create-file');
    
    // Payment Gateways
    Route::resource('payment-gateways', AdminPaymentGatewayController::class, ['as' => 'admin']);
    Route::get('payment-gateways/{id}/toggle', [AdminPaymentGatewayController::class, 'toggle'])->name('admin.payment-gateways.toggle');
    
    
    // Newsletter Management
    Route::resource('newsletter', NewsletterController::class, ['as' => 'admin']);
    Route::post('newsletter/send', [NewsletterController::class, 'send'])->name('admin.newsletter.send');
    Route::post('newsletter/{id}/toggle-status', [NewsletterController::class, 'toggleStatus'])->name('admin.newsletter.toggle-status');
    Route::get('newsletter/export', [NewsletterController::class, 'export'])->name('admin.newsletter.export');
    
    // Notifications
    Route::get('/notification/{id}', [NotificationController::class, 'show'])->name('admin.notification');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('all.notification');
    Route::delete('/notification/{id}', [NotificationController::class, 'delete'])->name('notification.delete');
    
    // Profile Management
    Route::get('/profile', [AdminController::class, 'profile'])->name('admin-profile');
    Route::post('/profile/{id}', [AdminController::class, 'profileUpdate'])->name('profile-update');
    
    // Password Change
    Route::get('change-password', [AdminController::class, 'changePassword'])->name('change.password.form');
    Route::post('change-password', [AdminController::class, 'changPasswordStore'])->name('change.password');
});

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
| Routes for user panel (protected by user middleware)
|
*/

Route::group(['prefix' => '/user', 'middleware' => ['user']], function () {
    
    // Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('user');
    
    // Profile Management
    Route::get('/profile', [HomeController::class, 'profile'])->name('user-profile');
    Route::post('/profile/{id}', [HomeController::class, 'profileUpdate'])->name('user-profile-update');
    
    // Order Management
    Route::prefix('order')->group(function () {
        Route::get('/', [HomeController::class, 'orderIndex'])->name('user.order.index');
        Route::get('/show/{id}', [HomeController::class, 'orderShow'])->name('user.order.show');
        Route::delete('/delete/{id}', [HomeController::class, 'userOrderDelete'])->name('user.order.delete');
    });
    
    // Product Review Management
    Route::prefix('user-review')->group(function () {
        Route::get('/', [HomeController::class, 'productReviewIndex'])->name('user.productreview.index');
        Route::delete('/delete/{id}', [HomeController::class, 'productReviewDelete'])->name('user.productreview.delete');
        Route::get('/edit/{id}', [HomeController::class, 'productReviewEdit'])->name('user.productreview.edit');
        Route::patch('/update/{id}', [HomeController::class, 'productReviewUpdate'])->name('user.productreview.update');
    });
    
    // Post Comment Management
    Route::prefix('user-post/comment')->group(function () {
        Route::get('/', [HomeController::class, 'userComment'])->name('user.post-comment.index');
        Route::delete('/delete/{id}', [HomeController::class, 'userCommentDelete'])->name('user.post-comment.delete');
        Route::get('/edit/{id}', [HomeController::class, 'userCommentEdit'])->name('user.post-comment.edit');
        Route::patch('/udpate/{id}', [HomeController::class, 'userCommentUpdate'])->name('user.post-comment.update');
    });
    
    // Password Change
    Route::get('change-password', [HomeController::class, 'changePassword'])->name('user.change.password.form');
    Route::post('change-password', [HomeController::class, 'changPasswordStore'])->name('user.change.password');
    
    // User Management
    Route::get('/users/create', [HomeController::class, 'userUsersCreate'])->name('user.users.create');
    Route::get('/users/edit/{id}', [HomeController::class, 'userUsersEdit'])->name('user.users.edit');
});

/*
|--------------------------------------------------------------------------
| File Manager Routes
|--------------------------------------------------------------------------
|
| Routes for Laravel File Manager
|
*/

// Laravel File Manager routes are handled by the package automatically