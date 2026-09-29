<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Detect if we are running on Hostinger with a split 'laravel_app' vs 'public_html' structure
        // If index.php is in public_html and base path is ../laravel_app, we need to fix public_path()
        if (basename(base_path()) === 'laravel_app' && method_exists($this->app, 'usePublicPath')) {
            $this->app->usePublicPath(realpath(base_path('../public_html')));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.drivingapp');
        Paginator::defaultSimpleView('vendor.pagination.drivingapp-simple');

        // Force HTTPS in production (Railway, Heroku, etc.)
        if (config('app.env') === 'production' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
        
        // Ensure storage directories exist (for Docker/containerized deployments)
        $this->ensureStorageDirectoriesExist();
    }
    
    /**
     * Ensure all required storage directories exist.
     * This is necessary for containerized deployments where storage might not persist.
     */
    protected function ensureStorageDirectoriesExist(): void
    {
        $directories = [
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            storage_path('app'),
            storage_path('app/public'),
        ];
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                @mkdir($directory, 0755, true);
            }
        }
    }
}
