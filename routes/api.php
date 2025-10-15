<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return new UserResource($request->user());
        });

        Route::apiResource('transactions', TransactionController::class);

        Route::apiResource('groups', GroupController::class);
    });
});
