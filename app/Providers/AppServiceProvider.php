<?php

namespace App\Providers;

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
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());

        \Illuminate\Support\Facades\Blade::component('layouts.hr', 'app-layout');
        \Illuminate\Support\Facades\Blade::component('layouts.applicant', 'applicant-layout');
        \Illuminate\Support\Facades\Blade::component('layouts.auth', 'auth-layout');
        \Illuminate\Support\Facades\Blade::component('layouts.guest', 'guest-layout');

        // Share unread notifications count with the HR layout
        \Illuminate\Support\Facades\View::composer('layouts.hr', function ($view) {
            $unreadNotifications = \Illuminate\Support\Facades\Auth::check()
                ? \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())->unread()->count()
                : 0;
            $view->with('unreadNotifications', $unreadNotifications);
        });
    }
}
