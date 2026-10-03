<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Administrator melihat seluruh riwayat; User hanya riwayat miliknya.
 */
class ActivityLogPolicy
{
    public function view(User $user, ActivityLog $log): Response
    {
        return $user->isAdministrator() || $user->id === $log->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
