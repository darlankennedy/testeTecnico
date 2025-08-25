<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AuthController;
use Modules\Admin\Http\Controllers\UserController;
use Modules\Admin\Http\Controllers\RoleController;
use Modules\Admin\Http\Controllers\PermissionController;
use Modules\Admin\Http\Controllers\MenuController;

Route::prefix('')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('auth/me', [AuthController::class, 'me'])->middleware('auth:api');

    Route::prefix('users')->group(function () {
        Route::get('index', [UserController::class, 'index'])->name('users.index');
        Route::post('store', [UserController::class, 'store'])->name('users.store');
        Route::put('update/{id}', [UserController::class, 'update'])->name('users.update');
        Route::get('show/{id}', [UserController::class, 'show'])->name('users.show');
    })->middleware('auth:api');


    Route::prefix('menu')->group(function (){
        Route::get('/', [MenuController::class, 'index']);
        Route::post('/refresh', [MenuController::class, 'refresh']);
    })->middleware('auth:api');

    Route::prefix('roles')->group(function () {
        Route::get('', [RoleController::class, 'index'])->middleware('permission:roles.read');
        Route::get('/all', [RoleController::class, 'all'])->middleware('permission:roles.read');
        Route::post('', [RoleController::class, 'store'])->middleware('permission:roles.create');
        Route::get('/{role}', [RoleController::class, 'show'])->middleware('permission:roles.read');
        Route::put('/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');

        Route::post('/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])
            ->middleware('permission:roles.update');
    })->middleware('auth:api');

    Route::prefix('permissions')->group(function () {
        Route::get('', [PermissionController::class, 'index'])->middleware('permission:permissions.read');
        Route::get('/all', [PermissionController::class, 'all'])->middleware('permission:permissions.read');
        Route::post('/store', [PermissionController::class, 'store'])->middleware('permission:permissions.create');
        Route::get('show/{permission}', [PermissionController::class, 'show'])->middleware('permission:permissions.read');
        Route::put('/update/{permission}', [PermissionController::class, 'update'])->middleware('permission:permissions.update');
        Route::delete('/destroy/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete');
    })->middleware('auth:api');



});
