<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Notification::where('user_id', $request->user()->id);

        if ($request->query('filter') === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $query->latest('created_at')->latest('id')->paginate(15)->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        $notification->update(['is_read' => true]);

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        Notification::where('user_id', $request->user()->id)->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
