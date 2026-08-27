<?php

use App\Http\Controllers\Api\PublicDonationController;
use Illuminate\Support\Facades\Route;

Route::get('donation-settings', [PublicDonationController::class, 'settings']);
Route::get('donation-purposes', [PublicDonationController::class, 'purposes']);
Route::post('donations', [PublicDonationController::class, 'store'])->middleware('throttle:10,1');
Route::post('donation-status', [PublicDonationController::class, 'status'])->middleware('throttle:5,1');
