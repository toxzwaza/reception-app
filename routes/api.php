<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\DisplayController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LocalStorageAuthController;
use App\Http\Controllers\ReceiveController;
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

// 出退勤打刻API
Route::post('/timeclock/login', [AttendanceController::class, 'login'])->name('api.timeclock.login');
Route::get('/timeclock/me', [AttendanceController::class, 'me'])->name('api.timeclock.me');
Route::post('/timeclock/clock-in', [AttendanceController::class, 'clockIn'])->name('api.timeclock.clock-in');
Route::post('/timeclock/clock-out', [AttendanceController::class, 'clockOut'])->name('api.timeclock.clock-out');
Route::post('/timeclock/cancel', [AttendanceController::class, 'cancel'])->name('api.timeclock.cancel');

// 当日の出退勤情報取得API（状態確認画面・外部連携用）
Route::get('/attendances/today', [AttendanceController::class, 'today'])->name('api.attendances.today');

// 事務所ディスプレイ表示システム用API（固定トークン認証）
Route::middleware('display.token')->get('/display/facility-schedules', [DisplayController::class, 'facilitySchedules'])->name('api.display.facility-schedules');

// 納品関連API
Route::get('/initial-orders', [ReceiveController::class, 'getInitialOrders'])->name('api.initial-orders');
Route::get('/com-names', [ReceiveController::class, 'getComNames'])->name('api.com-names');
// 物品(stock)の格納先候補取得API（在庫加算先の選択用）
Route::get('/stocks/{stock}/storages', [ReceiveController::class, 'getStockStorages'])->name('api.stock-storages');
