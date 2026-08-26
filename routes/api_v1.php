<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

// Site branding (public — logo/favicon only, never payment secrets)
Route::get('/settings/branding', [SettingController::class, 'branding']);
Route::get('/settings/payment-qr', [SettingController::class, 'qrPayment']);

// Catalog (public)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/products/{slug}/related', [ProductController::class, 'related']);
Route::get('/collections/{slug}', [CollectionController::class, 'show']);

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Cart & checkout (guest or authenticated — resolve.sanctum only POPULATES
// $request->user() when a valid token is sent; unlike auth:sanctum it never
// blocks an anonymous request).
Route::middleware('resolve.sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::patch('/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon']);

    Route::get('/checkout/payment-methods', [CheckoutController::class, 'paymentMethods']);
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::post('/checkout/payment/verify', [CheckoutController::class, 'verifyPayment']);
    Route::post('/checkout/payment/qr-submit', [CheckoutController::class, 'submitQrPayment']);
});

// Reviews (read public, write requires auth)
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);

// Newsletter + CMS pages
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::get('/pages/{slug}', [PageController::class, 'show']);

// Contact form
Route::post('/contact', [ContactController::class, 'store']);

// Authenticated-only routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::patch('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
    Route::get('/orders/{orderNumber}/invoice', [OrderController::class, 'invoice']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{productId}', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{productId}', [WishlistController::class, 'destroy']);

    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
});
