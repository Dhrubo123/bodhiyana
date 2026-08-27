<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth')->group(function () {
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('dashboard', [AdminDashboardController::class, 'dashboard']);
        Route::get('donations', [AdminDashboardController::class, 'donations']);
        Route::get('donations/{donation}', [AdminDashboardController::class, 'show']);
        Route::post('donations/{donation}/confirm', [AdminDashboardController::class, 'confirm']);
        Route::post('donations/{donation}/reject', [AdminDashboardController::class, 'reject']);
        Route::get('donations/{donation}/screenshot', [AdminDashboardController::class, 'screenshot']);
        Route::get('donors', [AdminManagementController::class, 'donors']);
        Route::get('purposes', [AdminManagementController::class, 'purposes']);
        Route::post('purposes', [AdminManagementController::class, 'storePurpose']);
        Route::put('purposes/{purpose}', [AdminManagementController::class, 'updatePurpose']);
        Route::delete('purposes/{purpose}', [AdminManagementController::class, 'destroyPurpose']);
        Route::get('events', [AdminManagementController::class, 'events']);
        Route::post('events', [AdminManagementController::class, 'storeEvent']);
        Route::put('events/{event}', [AdminManagementController::class, 'updateEvent']);
        Route::delete('events/{event}', [AdminManagementController::class, 'destroyEvent']);
        Route::get('website', [AdminManagementController::class, 'website']);
        Route::put('website', [AdminManagementController::class, 'updateWebsite']);
        Route::get('donation-settings', [AdminManagementController::class, 'donationSettings']);
        Route::put('payment-settings/{setting}', [AdminManagementController::class, 'updatePaymentSetting']);
        Route::post('payment-settings/{setting}', [AdminManagementController::class, 'updatePaymentSetting']);
        Route::post('bank-accounts', [AdminManagementController::class, 'storeBank']);
        Route::put('bank-accounts/{bank}', [AdminManagementController::class, 'updateBank']);
        Route::delete('bank-accounts/{bank}', [AdminManagementController::class, 'destroyBank']);
        Route::get('receipts', [AdminManagementController::class, 'receipts']);
        Route::get('reports', [AdminManagementController::class, 'reports']);
        Route::get('receipts/{donation}/pdf', [ReceiptController::class, 'adminPdf']);
    });
});

Route::view('/{any?}', 'welcome')->where('any', '.*');
