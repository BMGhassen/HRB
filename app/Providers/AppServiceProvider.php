<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure 'users' and 'notifications' tables exist before querying
    if (Schema::hasTable('users') && Schema::hasTable('notifications')) {
        $admin = User::where('role', 'admin')->first();

        if ($admin) {
            $unreadNotifications = $admin->unreadNotifications;
            $unreadNotificationsCount = $unreadNotifications->count();
        } else {
            $unreadNotifications = collect(); // empty collection
            $unreadNotificationsCount = 0;
        }

        View::share('unreadNotifications', $unreadNotifications);
        View::share('unreadNotificationsCount', $unreadNotificationsCount);
    }
    }
}
