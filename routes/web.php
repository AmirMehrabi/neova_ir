<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/workspace', [DashboardController::class, 'storeWorkspace'])->name('dashboard.workspace.store');
    Route::delete('/dashboard/workspace/{slug}', [DashboardController::class, 'destroyWorkspace'])->name('dashboard.workspace.destroy');
    Route::post('/dashboard/workspace/{workspaceSlug}/project', [DashboardController::class, 'storeProject'])->name('dashboard.project.store');
    Route::delete('/dashboard/workspace/{workspaceSlug}/project/{projectSlug}', [DashboardController::class, 'destroyProject'])->name('dashboard.project.destroy');

    Route::middleware('workspace')->group(function () {
        Route::middleware('project')->group(function () {
            Route::get('/{workspace}/{project}/board', [BoardController::class, 'show'])->name('board');
            Route::post('/{workspace}/{project}/task', [BoardController::class, 'storeTask'])->name('board.task.store');
            Route::put('/{workspace}/{project}/task/{task}', [BoardController::class, 'updateTask'])->name('board.task.update');
            Route::delete('/{workspace}/{project}/task/{task}', [BoardController::class, 'destroyTask'])->name('board.task.destroy');
            Route::post('/{workspace}/{project}/task/{task}/move', [BoardController::class, 'moveTask'])->name('board.task.move');
            Route::post('/{workspace}/{project}/column', [BoardController::class, 'storeColumn'])->name('board.column.store');
            Route::delete('/{workspace}/{project}/column/{column}', [BoardController::class, 'destroyColumn'])->name('board.column.destroy');
        });
    });
});
