<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middlewares alias
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'hotel.access' => \App\Http\Middleware\EnsureHotelAccess::class,
            'reception.or.admin' => \App\Http\Middleware\AllowReceptionOrHotelAdmin::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'log.activity' => \App\Http\Middleware\LogActivity::class,
        ]);

        // Appliquer les headers de sécurité globalement
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        // Logger les activités sensibles pour les utilisateurs authentifiés
        $middleware->appendToGroup('web', \App\Http\Middleware\LogActivity::class);
        
        // Valider les sessions utilisateur (doit être avant TrackUserSession)
        $middleware->appendToGroup('web', \App\Http\Middleware\ValidateUserSession::class);
        
        // Suivre les sessions utilisateur pour gérer les sessions multiples
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackUserSession::class);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Libérer les chambres expirées chaque jour à 00:00
        $schedule->command('rooms:release-expired')->daily();
        
        // Également toutes les 6 heures pour plus de réactivité
        $schedule->command('rooms:release-expired')->everySixHours();
        
        // Nettoyer les activités de plus de 24 heures - toutes les heures
        $schedule->command('activities:clean')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
