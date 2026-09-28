<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSiteSettingController;
use App\Http\Controllers\Admin\AdminSliderController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::get('/reset-password/{token}', function (string $token) {
    $email = request('email', '');
    return redirect('http://localhost:5173/reset-password?token=' . $token . '&email=' . urlencode($email));
})->name('password.reset');

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Protected Admin Panel Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', function () {
            return redirect()->route('admin.dashboard');
        });

        // Admin Team & Roles (Super Admin Only)
        Route::middleware('role:super_admin')->group(function () {
            Route::resource('users', AdminUserController::class);
            Route::get('/settings', [AdminSiteSettingController::class, 'index'])->name('settings.index');
            Route::post('/settings', [AdminSiteSettingController::class, 'update'])->name('settings.update');
        });

        // Products & Botanical Catalog
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');

        // Product Catalog Modifying Operations (Super Admin & Botanist)
        Route::middleware('role:super_admin,botanist')->group(function () {
            Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
            Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
            Route::match(['put', 'patch'], '/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
            Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
            Route::patch('/products/{product}/toggle-publish', [AdminProductController::class, 'togglePublish'])->name('products.toggle-publish');
            Route::patch('/products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
        });

        Route::get('/products/{product}', [AdminProductController::class, 'show'])->name('products.show');

        // Categories
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::middleware('role:super_admin,botanist')->group(function () {
            Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // Orders & Transit (Super Admin, Fulfillment, Support)
        Route::middleware('role:super_admin,fulfillment,support')->group(function () {
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        });

        // Order Fulfillment & Dispatch Status Modification (Super Admin & Fulfillment)
        Route::middleware('role:super_admin,fulfillment')->group(function () {
            Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        });

        // Inventory & Variant Stock (Super Admin, Botanist, Fulfillment)
        Route::middleware('role:super_admin,botanist,fulfillment')->group(function () {
            Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
            Route::patch('/inventory/{variant}', [AdminInventoryController::class, 'updateStock'])->name('inventory.update');
        });

        // Customers & Digital Gardeners (Super Admin & Support)
        Route::middleware('role:super_admin,support')->group(function () {
            Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
            Route::get('/customers/{user}', [AdminCustomerController::class, 'show'])->name('customers.show');
        });

        // Promotional Coupons & Discounts (Super Admin & Botanist)
        Route::middleware('role:super_admin,botanist')->group(function () {
            Route::patch('/coupons/{coupon}/toggle-active', [AdminCouponController::class, 'toggleActive'])->name('coupons.toggle-active');
            Route::resource('coupons', AdminCouponController::class);
        });

        // Home Page Hero Banners & Sliders (Super Admin & Botanist)
        Route::middleware('role:super_admin,botanist')->group(function () {
            Route::patch('/sliders/{slider}/toggle-active', [AdminSliderController::class, 'toggleActive'])->name('sliders.toggle-active');
            Route::post('/sliders/reorder', [AdminSliderController::class, 'updateSortOrder'])->name('sliders.reorder');
            Route::resource('sliders', AdminSliderController::class);
        });
    });
});
