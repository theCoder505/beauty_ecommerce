<?php

namespace App\Providers;

use App\Services\SettingService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingService::class, fn () => new SettingService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        try {
            if (Schema::hasTable('settings')) {
                $settingService = $this->app->make(SettingService::class);
                $settingService->configureSocialite();
                $settingService->configureStripe();
            }
        } catch (\Throwable) {
            // Silence during setup/migration commands before DB is ready
        }
    }
}
