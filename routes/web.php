<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('auth');
});

Route::get('/auth', [AuthController::class, 'showAuth'])->name('auth');
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])->name('auth.send-otp');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp');
Route::get('/auth/profile', [AuthController::class, 'showProfile'])->name('auth.profile');
Route::post('/auth/profile', [AuthController::class, 'completeProfile'])->name('auth.profile.store');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware('auth')->group(function () {
    Route::get('/board', function () {
        return view('board');
    })->name('board');
});
