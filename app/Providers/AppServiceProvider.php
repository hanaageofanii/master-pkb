<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Filament\Facades\Filament;
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
        FilamentView::registerRenderHook(
            'panels::auth.login.form.after',
            fn(): string => Blade::render('@vite(\'resources/css/custom-login.css\')'),
        );

        Filament::serving(function () {
            $logo = request()->is('admin/login') 
                ? asset('/image/logo.png')   
                : asset('/image/logo-pkb.png');

            Filament::registerRenderHook(
                'panels::brand',
                fn() => "<img src='{$logo}' alt='Logo' style='height: 10rem;'>"
            );
        });
    }
}
