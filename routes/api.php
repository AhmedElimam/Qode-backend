<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\UserController;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/userinfo', [AuthController::class, 'userInfo'])->middleware('auth:sanctum');
});

Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/search', [ArticleController::class, 'search']);
    Route::get('/personalized', [ArticleController::class, 'personalized'])->middleware('auth:sanctum');
    Route::get('/source/{source}', [ArticleController::class, 'bySource']);
    Route::get('/category/{category}', [ArticleController::class, 'byCategory']);
    Route::get('/categories', [ArticleController::class, 'categories']);
    Route::get('/{id}', [ArticleController::class, 'show']);
    Route::post('/refresh', [ArticleController::class, 'refresh'])->middleware('auth:sanctum');
});

Route::prefix('sources')->group(function () {
    Route::get('/', [ArticleController::class, 'sources']);
});

Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    Route::get('/preferences', [UserController::class, 'preferences']);
    Route::post('/preferences', [UserController::class, 'updatePreferences']);
    Route::put('/preferences', [UserController::class, 'updatePreferences']);
    Route::post('/sources/{source}/toggle', [UserController::class, 'toggleSource']);
    Route::post('/categories/{category}/toggle', [UserController::class, 'toggleCategory']);
    Route::get('/sources', [UserController::class, 'sources']);
    Route::get('/categories', [UserController::class, 'categories']);
});
