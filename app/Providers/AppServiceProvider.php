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
        \Illuminate\Support\Facades\Blade::component('layouts.app', 'app-layout');
        \Illuminate\Support\Facades\Blade::component('layouts.applicant', 'applicant-layout');
        \Illuminate\Support\Facades\Blade::component('layouts.auth', 'auth-layout');
        \Illuminate\Support\Facades\Blade::component('layouts.guest', 'guest-layout');
    }
}
