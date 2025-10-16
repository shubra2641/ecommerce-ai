<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\LanguageApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\UserApiController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| These routes are accessible without authentication
|
*/

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UserApiController::class, 'register']);

// Product Routes
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{slug}', [ProductApiController::class, 'show']);

// Category Routes
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/categories/{slug}', [CategoryApiController::class, 'show']);
Route::get('/categories/{slug}/products', [CategoryApiController::class, 'products']);

// Banner Routes
Route::get('/banners', [BannerApiController::class, 'index']);

// Language Routes
Route::get('/languages', [LanguageApiController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
|
| These routes require authentication via Sanctum
|
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication Routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Profile Routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserApiController::class, 'profile']);
        Route::put('/profile', [UserApiController::class, 'updateProfile']);
        Route::put('/password', [UserApiController::class, 'changePassword']);
    });

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::post('/', [CartApiController::class, 'add']);
        Route::get('/', [CartApiController::class, 'index']);
        Route::delete('/{id}', [CartApiController::class, 'remove']);
    });

    // Order Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderApiController::class, 'index']);
        Route::get('/{id}', [OrderApiController::class, 'show']);
        Route::post('/', [OrderApiController::class, 'store']);
        Route::put('/{id}/cancel', [OrderApiController::class, 'cancel']);
    });
});