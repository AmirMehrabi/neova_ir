<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceManagementController;
use App\Http\Controllers\TodayController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\WorkspaceHomeController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\WorkspaceSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/auth', [AuthController::class, 'showAuth'])->name('auth');
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])->name('auth.send-otp');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp');
Route::get('/auth/profile', [AuthController::class, 'showProfile'])->name('auth.profile');
Route::post('/auth/profile', [AuthController::class, 'completeProfile'])->name('auth.profile.store');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/join/{code}', [InvitationController::class, 'show'])->name('invitations.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.destroy');
    Route::patch('/profile/notifications', [ProfileController::class, 'updateNotificationPreferences'])->name('profile.notifications');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/workspace', [DashboardController::class, 'storeWorkspace'])->name('dashboard.workspace.store');
    Route::delete('/dashboard/workspace/{slug}', [DashboardController::class, 'destroyWorkspace'])->name('dashboard.workspace.destroy');
    Route::post('/dashboard/workspace/{workspaceSlug}/project', [DashboardController::class, 'storeProject'])->name('dashboard.project.store');
    Route::delete('/dashboard/workspace/{workspaceSlug}/project/{projectSlug}', [DashboardController::class, 'destroyProject'])->name('dashboard.project.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/decline', [InvitationController::class, 'decline'])->name('invitations.decline');

    Route::prefix('workspaces/{workspace}')->name('workspaces.')->group(function () {
        Route::get('/settings', [WorkspaceManagementController::class, 'index'])->name('settings');
        Route::patch('/settings', [WorkspaceManagementController::class, 'update'])->name('settings.update');
        Route::post('/invitations', [WorkspaceManagementController::class, 'invite'])->name('invitations.store');
        Route::post('/invitations/{invitation}/resend', [WorkspaceManagementController::class, 'resend'])->name('invitations.resend');
        Route::delete('/invitations/{invitation}', [WorkspaceManagementController::class, 'revoke'])->name('invitations.revoke');
        Route::patch('/members/{user}/role', [WorkspaceManagementController::class, 'updateRole'])->name('members.role');
        Route::delete('/members/{user}', [WorkspaceManagementController::class, 'removeMember'])->name('members.destroy');
        Route::post('/leave', [WorkspaceManagementController::class, 'leave'])->name('leave');
        Route::post('/projects/{project}/members', [WorkspaceManagementController::class, 'addProjectMember'])->name('projects.members.store');
        Route::delete('/projects/{project}/members/{user}', [WorkspaceManagementController::class, 'removeProjectMember'])->name('projects.members.destroy');
        Route::patch('/projects/{project}/visibility', [WorkspaceManagementController::class, 'updateProjectVisibility'])->name('projects.visibility');
    });

    Route::middleware('workspace')->group(function () {
        Route::get('/{workspace}/today', [TodayController::class, 'index'])->name('today');
        Route::get('/{workspace}/projects', [ProjectsController::class, 'index'])->name('projects.index');
        Route::get('/{workspace}/team', [TeamController::class, 'index'])->name('team.index');
        Route::get('/{workspace}/board', [WorkspaceHomeController::class, 'board'])->name('workspace.board');
        Route::get('/{workspace}/search', WorkspaceSearchController::class)->name('workspace.search');
        Route::middleware('workspace.editor')->post('/{workspace}/today/tasks', [TodayController::class, 'quickCreate'])->name('today.tasks.store');
        Route::middleware('project')->group(function () {
            Route::middleware('project.access')->group(function () {
                Route::get('/{workspace}/{project}/board', [BoardController::class, 'show'])->name('board');
                Route::get('/{workspace}/{project}/task/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'download'])->name('task.attachments.download');
                Route::middleware('workspace.editor')->group(function () {
                    Route::patch('/{workspace}/{project}/cycles/configure', [CycleController::class, 'configure'])->name('cycles.configure');
                    Route::post('/{workspace}/{project}/cycles', [CycleController::class, 'start'])->name('cycles.start');
                    Route::post('/{workspace}/{project}/cycles/{cycle}/finish', [CycleController::class, 'finish'])->name('cycles.finish');
                    Route::post('/{workspace}/{project}/task/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('task.attachments.store');
                    Route::delete('/{workspace}/{project}/task/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('task.attachments.destroy');
                    Route::put('/{workspace}/{project}/task/{task}/plan', [TodayController::class, 'plan'])->name('today.task.plan');
                    Route::delete('/{workspace}/{project}/task/{task}/plan', [TodayController::class, 'unplan'])->name('today.task.unplan');
                    Route::patch('/{workspace}/{project}/task/{task}/state', [TodayController::class, 'state'])->name('today.task.state');
                    Route::post('/{workspace}/{project}/task', [BoardController::class, 'storeTask'])->name('board.task.store');
                    Route::patch('/{workspace}/{project}/tasks/bulk', [BoardController::class, 'bulkUpdateTasks'])->name('board.tasks.bulk');
                    Route::put('/{workspace}/{project}/task/{task}', [BoardController::class, 'updateTask'])->name('board.task.update');
                    Route::post('/{workspace}/{project}/task/{task}/comments', [BoardController::class, 'addComment'])->name('board.task.comments.store');
                    Route::delete('/{workspace}/{project}/task/{task}', [BoardController::class, 'destroyTask'])->name('board.task.destroy');
                    Route::post('/{workspace}/{project}/task/{task}/move', [BoardController::class, 'moveTask'])->name('board.task.move');
                    Route::post('/{workspace}/{project}/column', [BoardController::class, 'storeColumn'])->name('board.column.store');
                    Route::post('/{workspace}/{project}/columns/reorder', [BoardController::class, 'reorderColumns'])->name('board.columns.reorder');
                    Route::patch('/{workspace}/{project}/column/{column}', [BoardController::class, 'updateColumn'])->name('board.column.update');
                    Route::delete('/{workspace}/{project}/column/{column}', [BoardController::class, 'destroyColumn'])->name('board.column.destroy');
                });
                Route::middleware('workspace.editor')->group(function () {
                    Route::patch('/{workspace}/{project}/settings', [BoardController::class, 'updateProject'])->name('board.project.update');
                    Route::post('/{workspace}/{project}/members', [BoardController::class, 'addProjectMember'])->name('board.project.members.store');
                    Route::delete('/{workspace}/{project}/members/{user}', [BoardController::class, 'removeProjectMember'])->name('board.project.members.destroy');
                    Route::get('/{workspace}/{project}/activity', [BoardController::class, 'activity'])->name('board.activity');
                });
            });
        });
    });
});
