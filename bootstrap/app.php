<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => \Modules\Admin\App\Http\Middleware\CheckPermission::class,
            //ban
            'forbid-banned-user' => \Cog\Laravel\Ban\Http\Middleware\ForbidBannedUser::class,
            'logs-out-banned-user' => \Cog\Laravel\Ban\Http\Middleware\LogsOutBannedUser::class,
            /**** OTHER MIDDLEWARE ALIASES ****/
            'localize' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
            'localizationRedirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localeCookieRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect::class,
            'localeViewPath' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,

        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\SetLocale::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->withSchedule(function (Schedule $schedule) {
        // Schedule the queue worker to run every minute
        $schedule->command('queue:work --sleep=3 --tries=3')->everyMinute()->withoutOverlapping();
        // Schedule the queue restart command to run daily
        $schedule->command('queue:restart')->everyTwoHours();
        //delete expired bans and unban models.
        $schedule->command('ban:delete-expired')->everyMinute();
        $schedule->command('telescope:prune --hours=48')->daily();
    })
    ->create();
