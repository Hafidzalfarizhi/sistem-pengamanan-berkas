<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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

        // ===== SEMENTARA: diganti route asli di tahap berikutnya =====
        Route::get('/enkripsi', fn () => view('sementara', ['judul' => 'Enkripsi File', 'tahap' => 11]))->name('encrypt.index');
        Route::get('/dekripsi', fn () => view('sementara', ['judul' => 'Dekripsi File', 'tahap' => 11]))->name('decrypt.index');
        Route::get('/files', fn () => view('sementara', ['judul' => 'File Saya', 'tahap' => 11]))->name('files.index');
        Route::get('/riwayat', fn () => view('sementara', ['judul' => 'Riwayat', 'tahap' => 12]))->name('history.index');
        Route::get('/notifications', fn () => view('sementara', ['judul' => 'Notifikasi', 'tahap' => 12]))->name('notifications.index');
        Route::get('/panduan', fn () => view('sementara', ['judul' => 'Panduan Penggunaan', 'tahap' => 12]))->name('guide');
        Route::get('/profile', fn () => view('sementara', ['judul' => 'Profil', 'tahap' => 12]))->name('profile.edit');
    };

    Route::prefix('admin')->name('admin.')->middleware('role:administrator')->group(function () use ($shared) {
        $shared();

        Route::resource('users', UserManagementController::class)->except(['show']);
        Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');
    });

    Route::prefix('user')->name('user.')->middleware('role:user')->group($shared);
});
