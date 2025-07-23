<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
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



        });
    
    });
    
// });
    