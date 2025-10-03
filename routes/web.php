<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InterviewPhoneController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DeliveryPickupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\OtherVisitorController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\TwilioTestController;
use App\Http\Controllers\TwilioVoiceController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// 認証ルート
require __DIR__.'/auth.php';

// localStorage認証用ルート（APIルートに移動済み）

// 管理画面（認証必須）
Route::middleware(['localstorage.auth'])->prefix('admin')->name('admin.')->group(function () {
    // ダッシュボード
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // 事前アポイント管理
    Route::resource('appointments', AdminAppointmentController::class);
    
    // 面接時の通話先電話番号管理
    Route::resource('interview-phones', InterviewPhoneController::class);
    
    // お知らせ管理
    Route::resource('announcements', AdminAnnouncementController::class);
});

// アポイントアリの方
Route::prefix('appointment')->name('appointment.')->group(function () {
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::post('/check-in-qr', [AppointmentController::class, 'checkInQr'])->name('check-in-qr');
    Route::post('/check-in-number', [AppointmentController::class, 'checkInNumber'])->name('check-in-number');
});

// 納品・集荷の方
Route::prefix('delivery-pickup')->name('delivery-pickup.')->group(function () {
    Route::get('/select', [DeliveryPickupController::class, 'select'])->name('select');
});

// 面接の方
Route::prefix('interview')->name('interview.')->group(function () {
    Route::get('/', [InterviewController::class, 'index'])->name('index');
});

// その他の方
Route::prefix('other-visitor')->name('other-visitor.')->group(function () {
    Route::get('/create', [OtherVisitorController::class, 'create'])->name('create');
    Route::post('/select-department', [OtherVisitorController::class, 'selectDepartment'])->name('select-department');
    Route::post('/store', [OtherVisitorController::class, 'store'])->name('store');
});

// 来訪者受付関連（既存）
Route::prefix('visitor')->name('visitor.')->group(function () {
    Route::get('/scan-qr', [VisitorController::class, 'scanQr'])->name('scan-qr');
    Route::post('/check-in', [VisitorController::class, 'checkIn'])->name('check-in');
    Route::get('/create', [VisitorController::class, 'create'])->name('create');
    Route::post('/store', [VisitorController::class, 'store'])->name('store');
    Route::get('/complete', [VisitorController::class, 'complete'])->name('complete');
});

// 納品業者受付関連
Route::prefix('delivery')->name('delivery.')->group(function () {
    Route::get('/create', [DeliveryController::class, 'create'])->name('create');
    Route::get('/capture', [DeliveryController::class, 'capture'])->name('capture');
    Route::post('/store', [DeliveryController::class, 'store'])->name('store');
    Route::get('/{delivery}', [DeliveryController::class, 'show'])->name('show');
});

// 集荷業者受付関連
Route::prefix('pickup')->name('pickup.')->group(function () {
    Route::get('/create', [PickupController::class, 'create'])->name('create');
    Route::post('/store', [PickupController::class, 'store'])->name('store');
    Route::get('/{pickup}', [PickupController::class, 'show'])->name('show');
});

// Twilio電話機能テスト
Route::prefix('twilio-test')->name('twilio-test.')->group(function () {
    Route::get('/', [TwilioTestController::class, 'index'])->name('index');
    Route::post('/make-call', [TwilioTestController::class, 'makeCall'])->name('make-call');
    Route::post('/check-status', [TwilioTestController::class, 'checkCallStatus'])->name('check-status');
    Route::post('/send-sms', [TwilioTestController::class, 'sendSms'])->name('send-sms');
});

// Twilioリアルタイム音声通話
Route::prefix('twilio-voice')->name('twilio-voice.')->group(function () {
    Route::get('/', [TwilioVoiceController::class, 'index'])->name('index');
    Route::post('/token', [TwilioVoiceController::class, 'generateToken'])->name('token');
    Route::post('/outgoing', [TwilioVoiceController::class, 'handleOutgoingCall'])->name('outgoing');
    Route::post('/incoming', [TwilioVoiceController::class, 'handleIncomingCall'])->name('incoming');
    Route::post('/status', [TwilioVoiceController::class, 'callStatusCallback'])->name('status');
    Route::get('/test', [TwilioVoiceController::class, 'testDevice'])->name('test');
});

Route::get('/test', [ TestController::class, 'test'])->name('test');
