<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CacheController;
use App\Http\Controllers\ReportController;

Route::prefix('v1')->name('api.')->group(function () {

    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login',    [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me',      [AuthController::class, 'me'])->name('me');


        Route::get('/reports/summary', [ReportController::class, 'summary']);
        Route::get('/reports/summary.pdf', [ReportController::class, 'summaryPdf']); // opcional (server-PDF)

        // USERS
        Route::get('/users/with-products', [UserController::class, 'usersWithProducts'])
            ->name('users.with_products');

        Route::get('/users/without-products', [UserController::class, 'usersWithoutProducts'])
            ->name('users.without_products'); // removi 'api.' duplicado

        Route::get('/users/count', [UserController::class, 'usersCount'])
            ->name('users.count');

        Route::get('/users/all', [UserController::class, 'listSimple'])->name('list.all');

        Route::prefix('products')->group(function () {
            Route::get('count', [ProductController::class, 'count'])->name('products.count');
        });

        Route::apiResource('users', UserController::class)
            ->parameters(['users' => 'id'])
            ->whereNumber('id');


        Route::apiResource('products', ProductController::class)
            ->parameters(['products' => 'id'])
            ->whereNumber('id');

        Route::post('/cache/users-stats/flush', [CacheController::class, 'flush'])
            ->name('cache.users_stats.flush');
    });
});
