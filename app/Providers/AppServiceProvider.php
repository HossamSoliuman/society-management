<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('superadmin.*', function ($view) {
            if (Auth::check()) {
                $view->with('authUser', Auth::user());
            }
        });
    }
}
