<?php

use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureRoleOrPermission;
use App\Http\Middleware\EnsureSocietyAccess;
use App\Models\SystemLog;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        // Persist server-side failures (5xx) to system_logs for the admin log screens.
        $exceptions->reportable(function (Throwable $exception) {
            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            if ($status < 500) {
                return;
            }

            try {
                if (! Schema::hasTable('system_logs')) {
                    return;
                }

                $user = auth()->user();
                $request = app()->runningInConsole() ? null : request();

                SystemLog::create([
                    'level' => 'error',
                    'module' => $request?->route()?->getName() ?? ($request?->path() ?? 'console'),
                    'user_name' => $user?->name,
                    'user_email' => $user?->email,
                    'message' => substr(get_class($exception).': '.$exception->getMessage(), 0, 2000),
                    'ip_address' => $request?->ip(),
                    'context' => substr($exception->getFile().':'.$exception->getLine()."\n".$exception->getTraceAsString(), 0, 8000),
                ]);
            } catch (Throwable) {
                // Never let logging itself break error handling.
            }
        });
    })->create();
