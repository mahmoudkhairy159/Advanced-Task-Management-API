<?php


namespace Modules\Task\App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Task\App\Console\Commands\CheckTaskDeadlines;
use Modules\Task\App\Console\Commands\SendTaskNotifications;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckTaskDeadlines::class,
                SendTaskNotifications::class,
            ]);
        }
    }
}
