<?php

namespace Modules\Task\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Task\App\Http\Middleware\TaskRateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Task';
    protected string $moduleNamespace = 'Modules\Task\App\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();

        // Register the rate limiter middleware
        $this->app['router']->aliasMiddleware('task.rate.limit', TaskRateLimiter::class);
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapAdminApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api/user')
            ->middleware('api')
            ->namespace($this->moduleNamespace . '\Api')
            ->group(module_path($this->name, '/routes/api.php'));
    }
    protected function mapAdminApiRoutes(): void
    {
        Route::prefix('api/admin')
            ->middleware('api')
            ->namespace($this->moduleNamespace . '\Admin')
            ->group(module_path($this->name, '/routes/admin-api.php'));
    }
}
