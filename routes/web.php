<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ===== SEMENTARA UNTUK TAHAP 8: file ini diganti seluruhnya di Tahap 9 =====

Route::get('/', fn () => redirect('/uji-tampilan'));
Route::get('/login', fn () => 'Halaman login (dibuat di Tahap 9)')->name('login');
Route::get('/dashboard', fn () => redirect('/uji-tampilan'))->name('dashboard');
Route::post('/logout', fn () => redirect('/login'))->name('logout');

// Route kosong agar semua tautan sidebar tidak error.
foreach (['admin', 'user'] as $prefix) {
    foreach (['dashboard', 'encrypt.index', 'decrypt.index', 'files.index', 'history.index', 'notifications.index', 'guide', 'profile.edit'] as $name) {
        Route::get('/' . $prefix . '/' . str_replace('.', '-', $name), fn () => 'Halaman ' . $name . ' (dibuat di tahap berikutnya)')
            ->name($prefix . '.' . $name);
    }
}
Route::get('/admin/users', fn () => 'Manajemen User (dibuat di Tahap 10)')->name('admin.users.index');

// Halaman uji.
Route::get('/uji-tampilan', function () {
    $role = request('role') === 'user' ? 'user' : 'administrator';
    $user = User::where('role', $role)->first() ?? User::first();
    Auth::login($user);

    return view('uji-tampilan');
});
Route::get('/uji-toast', fn () => redirect('/uji-tampilan')
    ->with('success', 'Toast sukses tampil.')
    ->with('error', 'Toast error tampil.')
    ->with('info', 'Toast info tampil.'));
Route::post('/uji-konfirmasi', fn () => redirect('/uji-tampilan')->with('success', 'Konfirmasi diterima, form terkirim.'));
Route::post('/uji-loading', function () {
    sleep(3);

    return redirect('/uji-tampilan')->with('success', 'Proses selesai.');
});
Route::get('/uji-error/{code}', fn (int $code) => abort($code))->whereIn('code', [403, 404, 419, 429, 500, 503]);
