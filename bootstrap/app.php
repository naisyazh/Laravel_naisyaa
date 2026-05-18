<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust reverse proxy headers (e.g. Cloudflare Tunnel) so URL generation uses the original HTTPS scheme.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'user'  => UserMiddleware::class,
        ]);
        $middleware->redirectTo(
            guests: '/login',
            // users: '/otp-verification'
            users: '/dashboard'
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
