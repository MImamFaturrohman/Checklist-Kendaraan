<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('layouts.dash-app', function ($view): void {
            $user = auth()->user();

            if ($user?->role !== 'superadmin') {
                $view->with([
                    'superadminNotifications' => collect(),
                    'superadminUnreadCount' => 0,
                ]);

                return;
            }

            $view->with([
                'superadminNotifications' => $user->notifications()->latest()->limit(20)->get(),
                'superadminUnreadCount' => $user->unreadNotifications()->count(),
            ]);
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');

            Log::info('Migrating and seeding database in production environment');
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            Log::info('Database migrated and seeded successfully');
        }
    }
}
