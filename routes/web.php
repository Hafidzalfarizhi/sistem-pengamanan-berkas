<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DecryptionController;
use App\Http\Controllers\EncryptionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    // Rute yang sama dipakai Administrator (/admin/...) dan User (/user/...).
    $shared = function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/enkripsi', [EncryptionController::class, 'index'])->name('encrypt.index');
        Route::post('/enkripsi', [EncryptionController::class, 'store'])->name('encrypt.store');

        Route::get('/dekripsi', [DecryptionController::class, 'index'])->name('decrypt.index');
        Route::post('/dekripsi', [DecryptionController::class, 'store'])->name('decrypt.store');

        Route::get('/files', [FileController::class, 'index'])->name('files.index');
        Route::get('/files/{file}', [FileController::class, 'show'])->name('files.show');
        Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
        Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

        Route::get('/riwayat', [ActivityLogController::class, 'index'])->name('history.index');
        Route::get('/riwayat/{activityLog}', [ActivityLogController::class, 'show'])->name('history.show');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

        Route::get('/panduan', [GuideController::class, 'index'])->name('guide');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    };

    Route::prefix('admin')->name('admin.')->middleware('role:administrator')->group(function () use ($shared) {
        $shared();

        Route::resource('users', UserManagementController::class)->except(['show']);
        Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');
    });

    Route::prefix('user')->name('user.')->middleware('role:user')->group($shared);
});
