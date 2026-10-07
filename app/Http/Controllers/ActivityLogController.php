<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    private const SORTABLE = ['created_at', 'activity', 'file_name', 'file_size', 'status'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdministrator();

        $query = ActivityLog::query();
        if ($isAdmin) {
            $query->with('user:id,name,username');
        } else {
            // User hanya melihat riwayat miliknya.
            $query->where('user_id', $user->id);
        }

        if ($isAdmin && $request->filled('user_id')) {
            $query->where('user_id', (int) $request->query('user_id'));
        }
        if ($request->filled('activity')) {
            $query->where('activity', $request->query('activity'));
        }
        if (in_array($request->query('status'), ['success', 'failed'], true)) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('file_name')) {
            $query->where('file_name', 'like', '%' . addcslashes(trim($request->query('file_name')), '%_\\') . '%');
        }
        if ($from = $this->parseDate($request->query('date_from'))) {
            $query->where('created_at', '>=', $from->startOfDay());
        }
        if ($to = $this->parseDate($request->query('date_to'))) {
            $query->where('created_at', '<=', $to->endOfDay());
        }
        if ($term = trim((string) $request->query('q'))) {
            $like = '%' . addcslashes($term, '%_\\') . '%';
            $query->where(function ($q) use ($like, $isAdmin) {
                $q->where('activity', 'like', $like)
                    ->orWhere('file_name', 'like', $like)
                    ->orWhere('description', 'like', $like);

                if ($isAdmin) {
                    $q->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('username', 'like', $like));
                }
            });
        }

        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $logs = $query->orderBy($sort, $dir)->orderBy('id', $dir)
            ->paginate(15)->withQueryString();

        return view('history.index', [
            'logs' => $logs,
            'activities' => ActivityLogger::ACTIVITIES,
            'users' => $isAdmin ? User::orderBy('name')->limit(500)->get(['id', 'name', 'username']) : collect(),
        ]);
    }

    public function show(ActivityLog $activityLog): View
    {
        Gate::authorize('view', $activityLog);

        return view('history.show', ['log' => $activityLog->load('user:id,name,username')]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
