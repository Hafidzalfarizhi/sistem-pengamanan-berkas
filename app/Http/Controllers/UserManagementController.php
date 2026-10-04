<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Hanya dapat diakses Administrator (middleware role:administrator pada route group).
 */
class UserManagementController extends Controller
{
    public function __construct(private ActivityLogger $logger, private NotificationService $notifier)
    {
    }

    public function index(Request $request): View
    {
        $query = User::query()->search($request->query('q'));

        if (in_array($request->query('role'), [User::ROLE_ADMIN, User::ROLE_USER], true)) {
            $query->where('role', $request->query('role'));
        }
        if (in_array($request->query('status'), [User::STATUS_ACTIVE, User::STATUS_INACTIVE], true)) {
            $query->where('status', $request->query('status'));
        }

        $sort = in_array($request->query('sort'), ['name', 'username', 'role', 'status', 'created_at'], true)
            ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $users = $query->orderBy($sort, $dir)->orderBy('id')->paginate(config('securefile.per_page'))->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.form', ['managed' => new User(['role' => User::ROLE_USER, 'status' => User::STATUS_ACTIVE])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],
        ]);

        $admin = $request->user();
        $user = User::create($data);

        $this->logger->record($admin, 'Tambah User', description: 'Menambahkan user ' . $user->name . ' (' . $user->username . ').');
        $this->notifier->send($admin, 'User ditambahkan', 'User berhasil ditambahkan.', 'success');
        $this->notifier->send($user, 'Selamat datang', 'Akun Anda telah dibuat oleh Administrator.', 'info');

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['managed' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:100', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],
        ]);

        $admin = $request->user();

        // Administrator tidak boleh menurunkan role atau menonaktifkan akunnya sendiri.
        if ($user->id === $admin->id && ($data['role'] !== $user->role || $data['status'] !== $user->status)) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['status' => 'Anda tidak dapat mengubah role atau status akun Anda sendiri.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        $this->logger->record($admin, 'Edit User', description: 'Mengubah user ' . $user->name . ' (' . $user->username . ').');
        $this->notifier->send($admin, 'User diperbarui', 'Data user berhasil diperbarui.', 'success');

        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        if ($user->id === $admin->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $activate = ! $user->isActive();
        $user->update(['status' => $activate ? User::STATUS_ACTIVE : User::STATUS_INACTIVE]);

        $activity = $activate ? 'Aktifkan User' : 'Nonaktifkan User';
        $this->logger->record($admin, $activity, description: $activity . ': ' . $user->name . ' (' . $user->username . ').');
        $this->notifier->send($admin, 'Status user diubah', 'User ' . $user->name . ' berhasil ' . ($activate ? 'diaktifkan.' : 'dinonaktifkan.'), 'success');

        return back()->with('success', 'User berhasil ' . ($activate ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        if ($user->id === $admin->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $label = $user->name . ' (' . $user->username . ')';
        $paths = $user->files()->pluck('storage_path')->all();

        DB::transaction(function () use ($user, $admin, $label) {
            // Riwayat milik user ikut terhapus (cascade); catatan penghapusan disimpan atas nama admin.
            $this->logger->record($admin, 'Hapus User', description: 'Menghapus user ' . $label . '.');
            $this->notifier->send($admin, 'User dihapus', 'User berhasil dihapus.', 'success');

            $user->delete();
        });

        // File fisik dihapus setelah data di database berhasil dihapus.
        $disk = Storage::disk(config('securefile.disk'));
        foreach ($paths as $path) {
            $disk->delete($path);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
