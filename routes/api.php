<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Маршруты, доступные для всех (неаутентифицированных пользователей)
Route::post('/login', [AuthController::class, 'login']);

// Маршруты, доступные только для аутентифицированных пользователей
Route::middleware('auth:sanctum')->group(function () {
    // Стандартный маршрут для получения данных о текущем пользователе
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Наш маршрут для выхода из системы
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->prefix('admin')->group(function() {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        
        Route::get('/users/{user}', [UserController::class, 'show']);
        
        Route::put('/users/{user}', [UserController::class, 'update']);
        
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
});