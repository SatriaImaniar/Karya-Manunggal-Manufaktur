<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Gunakan tampilan pagination Bootstrap 5 (bukan Tailwind default)
        // untuk konsistensi dengan template Bootstrap yang dipakai di seluruh app.
        Paginator::useBootstrapFive();
    }
}
