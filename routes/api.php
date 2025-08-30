<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Seller\AuthController;
use App\Http\Controllers\Seller\CouponController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\GroupController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Middleware\InitializeTenantMiddleware;
use App\Http\Middleware\JWTAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Middleware\TenancyMiddleware;


// Route::middleware([
//     'web',
//     // TenancyMiddleware::class,
//     // 'throttle:global',
// ])->group(function () {
        
    Route::prefix('admin')->group(function () {
    
        Route::post('login',[AdminAuthController::class,'login']);
        Route::get('refresh',[AdminAuthController::class,'refresh']);
    
        Route::middleware([
            JWTAuthMiddleware::class,
        ])->group(function () {
    
            Route::get('me', [AdminAuthController::class, 'me']);
            Route::post('logout',[AdminAuthController::class,'logout']);


            Route::get('dashboard', [DashboardController::class, 'index' ]);
            Route::get('analytics', [DashboardController::class, 'analytics' ]);


            Route::controller(SellerController::class)->group(function(){
                Route::get('sellers', 'index');
                Route::put('sellers-bulk-email', 'bulkEmail');
                Route::get('sellers-log/{id}', 'sellerLog');
                Route::get('sellers-order/{id}', 'sellerOrder');
                Route::put('sellers-status/{id}', 'status');
                Route::get('sellers-delete/{id}', 'delete');
                // Route::get('sellers-login/{id}', 'sellerLogin');
                // Route::post('sellers-payment/{id}', 'paymentSetting');


                // Route::get('contact-list', 'contact');
                // Route::delete('contact-delete/{id}', 'contactDelete');
            });

        });
    
    });



    Route::prefix('seller')->group(function () {
        Route::post('register',[AuthController::class,'register']);
        Route::post('register-email-verify',[AuthController::class,'registerEmailVerify']);
        Route::post('login',[AuthController::class,'login']);
        Route::get('refresh',[AuthController::class,'refresh']);

        Route::put('forget-pass', [AuthController::class, 'forgetPassword']);
        Route::put('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::put('reset-pass', [AuthController::class, 'resetPassword']);
    
        Route::middleware([
            JWTAuthMiddleware::class,
            InitializeTenantMiddleware::class
        ])->group(function () {
    
            Route::get('me', [AdminAuthController::class, 'me']);
            Route::post('logout',[AdminAuthController::class,'logout']);

            Route::get('email-otp', [AdminAuthController::class, 'emailOtp']);
            Route::put('verify-otp', [AdminAuthController::class, 'verifyOtp']);

            Route::get('profile', [AdminAuthController::class, 'profile']);
            Route::put('profile-info-update', [AdminAuthController::class, 'profileInfoUpdate']);
            Route::put('profile-pass-update', [AdminAuthController::class, 'profilePassUpdate']);
            Route::post('profile-image-update', [AdminAuthController::class, 'profilePicUpdate']);


            Route::get('analytics', [SellerDashboardController::class, 'index' ]);
            // Route::get('analytics', [SellerDashboardController::class, 'analytics' ]);

            Route::controller(ProductController::class)->group(function(){
                Route::get('products', 'index');
                Route::get('products/view/{id}', 'view');
                Route::post('products', 'store');
                Route::post('products/{id}', 'update');
                Route::post('products-variant-edit/{id}', 'variantEdit');
                Route::delete('products-variant-delete/{id}', 'variantDelete');
                Route::delete('products/{id}/delete', 'destroy');
                Route::get('products/{id}/status', 'status');

                // Route::post('products-ordering', 'productOrdering')->name('products.ordering');

                // Route::post('products-variant-edit/{id}', 'productvariantEdit')->name('products.variant.edit');
            });


            Route::controller(GroupController::class)->group(function(){
                Route::get('group', 'index');
                Route::post('group', 'store');
                Route::get('group/{id}', 'view');
                Route::post('group/{id}', 'update');
                Route::delete('group/{id}/delete', 'destroy');
                // Route::get('group/{id}/status', 'status');
                Route::get('group-get-products', 'getProducts');
            });


            Route::controller(CouponController::class)->group(function(){
                Route::get('coupon-setting', 'index');
                Route::get('coupon-setting/{id}', 'view');
                Route::post('coupon-setting', 'store');
                Route::post('coupon-setting/{id}', 'update');
                Route::delete('coupon-setting/{id}/delete', 'destroy');
            });


            Route::controller(OrderController::class)->group(function(){
                Route::get('invoice', 'index');
                Route::get('invoice-analytics', 'analytics');
                Route::get('invoice-details/{id}', 'view');
                Route::post('invoice-bulk-email', 'bulkEmail');
                Route::get('invoice-download/{id}', 'downloadInvoice');
            });


        });
    
    });


    Route::fallback(function () {
        return response()->json([
            'status' => false,
            'message' => 'Not Found'
        ], 404);
    });
    
// });
    