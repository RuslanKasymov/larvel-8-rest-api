<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('register', ['uses' => AuthController::class . '@register']);
    Route::post('login', ['uses' => AuthController::class . '@login']);
    Route::post('logout', ['uses' => AuthController::class . '@logout']);
    Route::post('forgot-password', ['uses' => AuthController::class . '@forgotPassword']);
    Route::post('reset-password', ['uses' => AuthController::class . '@resetPassword']);
    Route::get('refresh', ['uses' => AuthController::class . '@refresh'])->middleware('jwt.refresh');
    Route::post('logout', ['uses' => AuthController::class . '@logout'])->middleware('auth:api');
});

Route::group(['middleware' => 'auth'], function () {

    Route::prefix('users')->group(function () {
        Route::post('/', ['uses' => UserController::class . '@create']);
        Route::put('/{id}', ['uses' => UserController::class . '@update']);
        Route::delete('/{id}', ['uses' => UserController::class . '@delete']);
        Route::get('/{id}', ['uses' => UserController::class . '@get']);
        Route::get('/', ['uses' => UserController::class . '@list']);
    });

    Route::prefix('profile')->group(function () {
        Route::get('/', ['uses' => UserController::class . '@profile']);
        Route::put('/', ['uses' => UserController::class . '@updateProfile']);
    });
});
