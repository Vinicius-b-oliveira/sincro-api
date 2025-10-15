<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Resources\V1\User\UserResource;
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
        Route::get('/user', [UserController::class, 'show']);
        Route::patch('/user/preferences', [UserController::class, 'updatePreferences']);

        Route::apiResource('transactions', TransactionController::class);

        Route::apiResource('groups', GroupController::class);
        Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember']);
        Route::get('/groups/{group}/members', [GroupController::class, 'listMembers']);
        Route::patch('/groups/{group}/members/{user}', [GroupController::class, 'updateMemberRole']);

        Route::post('/groups/{group}/invitations', [InvitationController::class, 'store']);
        Route::get('/invitations/pending', [InvitationController::class, 'pending']);
        Route::post('/invitations/{invitation}/accept', [InvitationController::class, 'accept']);
        Route::post('/invitations/{invitation}/decline', [InvitationController::class, 'decline']);
    });
});
