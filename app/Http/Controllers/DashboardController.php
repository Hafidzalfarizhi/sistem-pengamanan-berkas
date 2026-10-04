<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->routeName('dashboard'));
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdministrator()) {
            $stats = [
                'Total User' => ['value' => User::count(), 'icon' => 'bi-people'],
                'User Aktif' => ['value' => User::where('status', User::STATUS_ACTIVE)->count(), 'icon' => 'bi-person-check'],
                'User Nonaktif' => ['value' => User::where('status', User::STATUS_INACTIVE)->count(), 'icon' => 'bi-person-slash'],
                'Total File' => ['value' => File::count(), 'icon' => 'bi-files'],
                'File Terenkripsi' => ['value' => File::where('status', File::STATUS_ENCRYPTED)->count(), 'icon' => 'bi-file-earmark-lock2'],
                'File Terdekripsi' => ['value' => File::where('status', File::STATUS_DECRYPTED)->count(), 'icon' => 'bi-file-earmark-check'],
            ];
            $recent = ActivityLog::with('user:id,name')->latest('created_at')->latest('id')->limit(8)->get();
        } else {
            $files = $user->files();
            $stats = [
                'Total File' => ['value' => (clone $files)->count(), 'icon' => 'bi-files'],
                'File Terenkripsi' => ['value' => (clone $files)->where('status', File::STATUS_ENCRYPTED)->count(), 'icon' => 'bi-file-earmark-lock2'],
                'File Terdekripsi' => ['value' => (clone $files)->where('status', File::STATUS_DECRYPTED)->count(), 'icon' => 'bi-file-earmark-check'],
            ];
            $recent = $user->activityLogs()->latest('created_at')->latest('id')->limit(8)->get();
        }

        return view('dashboard.index', compact('stats', 'recent'));
    }
}
