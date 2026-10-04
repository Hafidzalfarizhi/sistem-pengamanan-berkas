<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username/NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            if ($user) {
                $this->logger->record($user, 'Login', 'failed', description: 'Password salah.');
            }

            return back()->withErrors(['username' => 'Username/NIP atau password salah.'])->onlyInput('username');
        }

        if (! $user->isActive()) {
            $this->logger->record($user, 'Login', 'failed', description: 'Akun nonaktif.');

            return back()->withErrors(['username' => 'Akun Anda tidak aktif. Hubungi Administrator.'])->onlyInput('username');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $this->logger->record($user, 'Login', description: 'Login berhasil.');

        return redirect()->route($user->routeName('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            $this->logger->record($user, 'Logout', description: 'Logout berhasil.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
