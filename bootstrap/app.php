<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware role untuk pembatasan akses berdasarkan role user
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Redirect guest (user belum login) ke halaman login
        // Ini menggantikan fungsi redirectTo() di Authenticate.php pada Laravel lama
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Redirect user yang sudah login (namun mencoba akses halaman guest) ke dashboard
        $middleware->redirectUsersTo(function () {
            return match (auth()->user()?->role) {
                'admin'   => route('admin.dashboard'),
                'teknisi' => route('teknisi.dashboard'),
                default   => route('login'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
