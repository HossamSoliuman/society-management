<?php

use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureRoleOrPermission;
use App\Http\Middleware\EnsureSocietyAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'active' => EnsureActiveAccount::class,
            'role' => EnsureRole::class,
            'permission' => EnsurePermission::class,
            'role_or_permission' => EnsureRoleOrPermission::class,
            'society.access' => EnsureSocietyAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
