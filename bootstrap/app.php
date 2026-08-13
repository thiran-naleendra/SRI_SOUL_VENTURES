<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

$webRoutes = dirname(__DIR__).'/routes/web.php';
$webRoutesModifiedAt = @filemtime($webRoutes) ?: 0;

// Shared-hosting deployments do not always provide terminal access to run
// route:clear. Discard only a stale route cache; a freshly generated cache is
// retained because its modification time will be newer than routes/web.php.
foreach (glob(__DIR__.'/cache/routes-*.php') ?: [] as $routeCache) {
    $cachedRoutes = @file_get_contents($routeCache) ?: '';
    $requiredAdminRoutes = [
        'packages/{package}/save',
        'destinations/{destination}/save',
    ];
    $isLegacyAdminCache = collect($requiredAdminRoutes)
        ->contains(fn (string $route) => ! str_contains($cachedRoutes, $route));

    if ($isLegacyAdminCache || (@filemtime($routeCache) ?: 0) < $webRoutesModifiedAt) {
        @unlink($routeCache);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
