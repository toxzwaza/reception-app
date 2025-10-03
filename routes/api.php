<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LocalStorageAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ユーザー一覧取得API
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{userId}', [UserController::class, 'show']);

// localStorage認証API
Route::post('/login-local', [LocalStorageAuthController::class, 'login']);
Route::post('/logout-local', [LocalStorageAuthController::class, 'logout']);
Route::post('/test-password', [LocalStorageAuthController::class, 'testPassword']); // デバッグ用
Route::post('/set-session-user', [LocalStorageAuthController::class, 'setSessionUser']); // セッション設定用
