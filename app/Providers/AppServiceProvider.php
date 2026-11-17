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
        // Admin notifications for every view. Loaded only when a view is rendered (once per
        // request), so artisan, composer and queue workers never need the database to boot.
        View::composer('*', function ($view) {
            static $loaded = null;

            if ($loaded === null) {
                $loaded = collect();
                if (Schema::hasTable('users') && Schema::hasTable('notifications')) {
                    $admin = User::where('role', 'admin')->first();
                    $loaded = $admin ? $admin->unreadNotifications : collect();
                }
            }

            $view->with('unreadNotifications', $loaded)
                 ->with('unreadNotificationsCount', $loaded->count());
        });
    }
}
