<?php

use App\Http\Controllers\Api\V1\Admin\AdminApiAuthController;
use App\Http\Controllers\Api\V1\Admin\AdminApiCategoryController;
use App\Http\Controllers\Api\V1\Admin\AdminApiCouponController;
use App\Http\Controllers\Api\V1\Admin\AdminApiCustomerController;
use App\Http\Controllers\Api\V1\Admin\AdminApiDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminApiInventoryController;
use App\Http\Controllers\Api\V1\Admin\AdminApiOrderController;
use App\Http\Controllers\Api\V1\Admin\AdminApiProductController;
use App\Http\Controllers\Api\V1\Admin\AdminApiProductTypeController;
use App\Http\Controllers\Api\V1\Admin\AdminApiSiteSettingController;
use App\Http\Controllers\Api\V1\Admin\AdminApiSliderController;
use App\Http\Controllers\Api\V1\Admin\AdminApiUserController;
use App\Http\Controllers\Api\V1\CartCheckoutController;
use App\Http\Controllers\Api\V1\CustomerAuthController;
use App\Http\Controllers\Api\V1\HomeSliderController;
use App\Http\Controllers\Api\V1\OrderTrackingController;
use App\Http\Controllers\Api\V1\ProductCatalogController;
use App\Http\Controllers\Api\V1\SiteSettingController;
use App\Http\Controllers\Api\V1\UserPlantCompanionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Botanical Catalog & Categories (Customer)
    Route::get('/categories', [ProductCatalogController::class, 'categories']);
    Route::get('/product-types', [ProductCatalogController::class, 'productTypes']);
    Route::get('/products', [ProductCatalogController::class, 'index']);
    Route::get('/products/{slug}', [ProductCatalogController::class, 'show']);
    Route::post('/plant-finder', [ProductCatalogController::class, 'plantFinder']);

    // Home Page Hero Banners & Promo Sliders (Customer)
    Route::get('/home/sliders', [HomeSliderController::class, 'index']);
    Route::get('/sliders', [HomeSliderController::class, 'index']);

    // Public Storefront Configuration & Policies (Customer)
    Route::get('/site-settings', [SiteSettingController::class, 'index']);
    Route::get('/settings', [SiteSettingController::class, 'index']);

    // Cart, Shipping Validation, Coupon Discounts & Atomic Checkout
    Route::post('/coupons/validate', [CartCheckoutController::class, 'validateCoupon']);
    Route::post('/shipping/check-deliverability', [CartCheckoutController::class, 'checkDeliverability']);
    Route::post('/checkout/summary', [CartCheckoutController::class, 'checkoutSummary']);
    Route::post('/checkout/orders', [CartCheckoutController::class, 'createOrder']);

    // Live-Plant Transit Tracking
    Route::get('/orders/{orderNumber}/track', [OrderTrackingController::class, 'track']);

    // Digital Garden Companion
    Route::get('/my-plants', [UserPlantCompanionController::class, 'index']);
    Route::post('/my-plants', [UserPlantCompanionController::class, 'store']);
    Route::patch('/my-plants/{id}/water', [UserPlantCompanionController::class, 'water']);

    // Customer Authentication & Social Login (Google, GitHub, Facebook)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [CustomerAuthController::class, 'register']);
        Route::post('/login', [CustomerAuthController::class, 'login']);
        Route::post('/social', [CustomerAuthController::class, 'socialLogin']);
        Route::post('/forgot-password', [CustomerAuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', [CustomerAuthController::class, 'user']);
            Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
            Route::post('/cart/sync', [CustomerAuthController::class, 'syncCart']);
            Route::post('/wishlist/sync', [CustomerAuthController::class, 'syncWishlist']);
            Route::get('/orders', [CustomerAuthController::class, 'orders']);
            Route::post('/logout', [CustomerAuthController::class, 'logout']);
        });
    });

    // Botanical Admin API (Headless & Mobile Administration with Role-Based Access)
    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminApiAuthController::class, 'login']);

        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            Route::get('/user', [AdminApiAuthController::class, 'user']);
            Route::post('/logout', [AdminApiAuthController::class, 'logout']);
            Route::get('/dashboard', [AdminApiDashboardController::class, 'stats']);

            // Admin User & Site Configuration (Super Admin only)
            Route::middleware('role:super_admin')->group(function () {
                Route::apiResource('users', AdminApiUserController::class);
                Route::get('/settings', [AdminApiSiteSettingController::class, 'index']);
                Route::post('/settings', [AdminApiSiteSettingController::class, 'update']);
            });

            // Product Catalog
            Route::get('/products', [AdminApiProductController::class, 'index']);
            Route::get('/products/{id}', [AdminApiProductController::class, 'show']);
            Route::middleware('role:super_admin,botanist')->group(function () {
                Route::post('/products', [AdminApiProductController::class, 'store']);
                Route::match(['put', 'patch'], '/products/{id}', [AdminApiProductController::class, 'update']);
                Route::delete('/products/{id}', [AdminApiProductController::class, 'destroy']);
            });

            // Categories
            Route::get('/categories', [AdminApiCategoryController::class, 'index']);
            Route::get('/categories/{id}', [AdminApiCategoryController::class, 'show']);
            Route::middleware('role:super_admin,botanist')->group(function () {
                Route::post('/categories', [AdminApiCategoryController::class, 'store']);
                Route::match(['put', 'patch'], '/categories/{id}', [AdminApiCategoryController::class, 'update']);
                Route::delete('/categories/{id}', [AdminApiCategoryController::class, 'destroy']);
            });

            // Product Types
            Route::get('/product-types', [AdminApiProductTypeController::class, 'index']);
            Route::get('/product-types/{id}', [AdminApiProductTypeController::class, 'show']);
            Route::middleware('role:super_admin,botanist')->group(function () {
                Route::post('/product-types', [AdminApiProductTypeController::class, 'store']);
                Route::match(['put', 'patch'], '/product-types/{id}', [AdminApiProductTypeController::class, 'update']);
                Route::delete('/product-types/{id}', [AdminApiProductTypeController::class, 'destroy']);
                Route::patch('/product-types/{id}/toggle-active', [AdminApiProductTypeController::class, 'toggleActive']);
            });

            // Promotional Coupons & Discounts (Super Admin & Botanist)
            Route::middleware('role:super_admin,botanist')->group(function () {
                Route::get('/coupons', [AdminApiCouponController::class, 'index']);
                Route::get('/coupons/{coupon}', [AdminApiCouponController::class, 'show']);
                Route::post('/coupons', [AdminApiCouponController::class, 'store']);
                Route::match(['put', 'patch'], '/coupons/{coupon}', [AdminApiCouponController::class, 'update']);
                Route::delete('/coupons/{coupon}', [AdminApiCouponController::class, 'destroy']);
            });

            // Home Page Hero Banners & Promo Sliders (Super Admin & Botanist)
            Route::middleware('role:super_admin,botanist')->group(function () {
                Route::get('/sliders', [AdminApiSliderController::class, 'index']);
                Route::get('/sliders/{slider}', [AdminApiSliderController::class, 'show']);
                Route::post('/sliders', [AdminApiSliderController::class, 'store']);
                Route::match(['put', 'patch'], '/sliders/{slider}', [AdminApiSliderController::class, 'update']);
                Route::delete('/sliders/{slider}', [AdminApiSliderController::class, 'destroy']);
            });

            // Orders
            Route::middleware('role:super_admin,fulfillment,support')->group(function () {
                Route::get('/orders', [AdminApiOrderController::class, 'index']);
                Route::get('/orders/{id}', [AdminApiOrderController::class, 'show']);
            });
            Route::middleware('role:super_admin,fulfillment')->group(function () {
                Route::patch('/orders/{id}/status', [AdminApiOrderController::class, 'updateStatus']);
            });

            // Inventory
            Route::middleware('role:super_admin,botanist,fulfillment')->group(function () {
                Route::get('/inventory', [AdminApiInventoryController::class, 'index']);
                Route::patch('/inventory/{id}', [AdminApiInventoryController::class, 'updateStock']);
            });

            // Customers
            Route::middleware('role:super_admin,support')->group(function () {
                Route::get('/customers', [AdminApiCustomerController::class, 'index']);
                Route::get('/customers/{id}', [AdminApiCustomerController::class, 'show']);
            });
        });
    });
});
