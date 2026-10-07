<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private ActivityLogger $logger, private NotificationService $notifier)
    {
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
        ]);

        $user->update($data);
        $this->notifier->send($user, 'Profil diperbarui', 'Profil berhasil diperbarui.', 'success');

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update(['password' => $request->input('password')]);

        $this->logger->record($user, 'Ubah Password', description: 'Password akun diubah.');
        $this->notifier->send($user, 'Password diubah', 'Password berhasil diubah.', 'success');

        return back()->with('success', 'Password berhasil diubah.');
    }
}
