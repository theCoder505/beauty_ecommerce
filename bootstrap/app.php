<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\CacheSessionKeepAliveMiddleware;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\TrackSiteVisitMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/users.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('admin*') || auth('admin')->check()) {
                return route('admin.dashboard');
            }

            return route('user.dashboard');
        });

        $middleware->appendToGroup('web', [
            CacheSessionKeepAliveMiddleware::class,
            TrackSiteVisitMiddleware::class,
        ]);

        $middleware->alias([
            'admin' => AdminAuthenticate::class,
            'admin.guest' => RedirectIfAdminAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
