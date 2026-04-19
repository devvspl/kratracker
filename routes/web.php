<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkLogController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Masters\KraController;
use App\Http\Controllers\Masters\SubKraController;
use App\Http\Controllers\Masters\LogicController;
use App\Http\Controllers\Masters\TaskStatusController;
use App\Http\Controllers\Masters\PriorityController;
use App\Http\Controllers\Masters\ApplicationController;
use App\Http\Controllers\Masters\ApplicationModuleController;
use App\Http\Controllers\Masters\UserController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Notifications API
    Route::get('/api/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/api/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Notifications full page
    Route::get('/notifications', [NotificationController::class, 'all'])->name('notifications.all');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllReadWeb'])->name('notifications.mark-all-read');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Work Logs
    Route::get('/work-logs', [WorkLogController::class, 'index'])->name('work-logs.index');
    
    // Modules API (for dynamic loading in work log form)
    Route::get('/api/modules', [ApplicationModuleController::class, 'byApplication'])->name('api.modules');
    Route::post('/api/modules', [ApplicationModuleController::class, 'storeApi'])->name('api.modules.store');
    
    // Work Log AJAX routes (no email verification required)
    Route::post('/work-logs/store', [WorkLogController::class, 'store'])->name('work-logs.store');
    Route::get('/work-logs/{workLog}/show', [WorkLogController::class, 'show'])->name('work-logs.show');
    Route::put('/work-logs/{workLog}/update', [WorkLogController::class, 'update'])->name('work-logs.update');
    Route::delete('/work-logs/{workLog}/delete', [WorkLogController::class, 'destroy'])->name('work-logs.destroy');
    Route::post('/work-logs/{workLog}/feedback', [WorkLogController::class, 'storeFeedback'])->name('work-logs.feedback');
    
    // Export Routes
    Route::get('/export/work-logs', [ExportController::class, 'exportWorkLogs'])->name('export.work-logs');
    Route::get('/export/kra-summary', [ExportController::class, 'exportKraSummary'])->name('export.kra-summary');
    Route::get('/export/analytics-pdf', [ExportController::class, 'exportAnalyticsPdf'])->name('export.analytics-pdf');
    
    // Master Data Routes (Admin only)
    Route::middleware(['role:Admin'])->prefix('masters')->name('masters.')->group(function () {
        Route::resource('kras', KraController::class);
        Route::resource('sub-kras', SubKraController::class);
        Route::resource('logics', LogicController::class);
        Route::resource('task-statuses', TaskStatusController::class);
        Route::resource('priorities', PriorityController::class);
        Route::resource('applications', ApplicationController::class);
        Route::resource('application-modules', ApplicationModuleController::class);
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

require __DIR__.'/auth.php';
