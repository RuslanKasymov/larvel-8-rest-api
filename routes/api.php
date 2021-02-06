<?php

use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('register', ['uses' => AuthController::class . '@register']);
    Route::post('login', ['uses' => AuthController::class . '@login']);
    Route::post('logout', ['uses' => AuthController::class . '@logout']);
    Route::post('forgot-password', ['uses' => AuthController::class . '@forgotPassword']);
    Route::post('reset-password', ['uses' => AuthController::class . '@resetPassword']);
    Route::get('refresh', ['uses' => AuthController::class . '@refresh'])->middleware('jwt.refresh');
    Route::post('logout', ['uses' => AuthController::class . '@logout'])->middleware('auth:api');
});

Route::group(['middleware' => 'auth:api'], function () {

});
