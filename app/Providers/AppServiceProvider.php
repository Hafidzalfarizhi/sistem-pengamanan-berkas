<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\Notification;
use App\Policies\ActivityLogPolicy;
use App\Policies\FilePolicy;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale(config('app.locale'));

        Gate::policy(File::class, FilePolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);

                // Jumlah notifikasi belum dibaca untuk header.
        View::composer('layouts.app', function ($view) {
            $view->with('unreadCount', Auth::check()
                ? Notification::where('user_id', Auth::id())->where('is_read', false)->count()
                : 0);
        });
    }
}
