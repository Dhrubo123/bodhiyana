<?php

use App\Http\Controllers\Api\PublicDonationController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('donation-settings', [PublicDonationController::class, 'settings']);
Route::get('website-settings', [PublicDonationController::class, 'websiteSettings']);
Route::get('website-assets/{path}', [PublicDonationController::class, 'websiteAsset'])->where('path', '.*');
Route::get('banners', [PublicDonationController::class, 'banners']);
Route::get('events', [PublicDonationController::class, 'events']);
Route::get('events/{event}', [PublicDonationController::class, 'event']);
Route::get('event-assets/{path}', [PublicDonationController::class, 'eventAsset'])->where('path', '.*');
Route::get('gallery', [PublicDonationController::class, 'gallery']);
Route::get('gallery-assets/{path}', [PublicDonationController::class, 'galleryAsset'])->where('path', '.*');
Route::get('donation-purposes', [PublicDonationController::class, 'purposes']);
Route::post('donations', [PublicDonationController::class, 'store'])->middleware('throttle:10,1');
Route::post('donation-status', [PublicDonationController::class, 'status'])->middleware('throttle:5,1');
Route::get('receipts/{token}/pdf', [ReceiptController::class, 'publicPdf'])->middleware('throttle:10,1');
