<?php

namespace App\Providers;

use App\Services\CacheSessionService;
use App\Services\SettingService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Session\CacheBasedSessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingService::class, fn () => new SettingService());
        $this->app->singleton(CacheSessionService::class, fn () => new CacheSessionService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Support 'cache' as a valid session driver option
        Session::extend('cache', function ($app) {
            return new CacheBasedSessionHandler(
                $app['cache']->store($app['config']['session.store'] ?? null),
                $app['config']['session.lifetime']
            );
        });

        // Keep session in cache on login; keep for 6 months and renew daily on activity
        Event::listen(Login::class, function (Login $event): void {
            if ($event->user) {
                $this->app->make(CacheSessionService::class)->createSession(
                    $event->user,
                    $event->guard ?? 'web'
                );
            }
        });

        // Forget cache session on logout
        Event::listen(Logout::class, function (Logout $event): void {
            $this->app->make(CacheSessionService::class)->destroyCurrentSession(
                $event->guard ?? 'web'
            );
        });

        // Register custom HTMLPurifier data URI scheme for safe image embedding (JPEG, PNG, GIF, WebP, AVIF)
        if (class_exists(\HTMLPurifier_URISchemeRegistry::class)) {
            \HTMLPurifier_URISchemeRegistry::instance()->register('data', new \App\Support\ExtendedHTMLPurifierURISchemeData());
        }

        try {
            if (Schema::hasTable('settings')) {
                $settingService = $this->app->make(SettingService::class);
                $settingService->configureTimezone();
                $settingService->configureSocialite();
                $settingService->configureStripe();
            }
        } catch (\Throwable) {
            // Silence during setup/migration commands before DB is ready
        }
    }
}
