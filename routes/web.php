<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// 来訪者受付関連
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