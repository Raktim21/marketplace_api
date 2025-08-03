<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Seller\AuthController;
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
                Route::post('sellers-bulk-email', 'bulkEmail');
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

            Route::get('dashboard', [DashboardController::class, 'index' ]);
            Route::get('analytics', [DashboardController::class, 'analytics' ]);

        });
    
    });


    Route::fallback(function () {
        return response()->json([
            'status' => false,
            'message' => 'Not Found'
        ], 404);
    });
    
// });
    