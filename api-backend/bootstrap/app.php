<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\UnauthorizedException as SpatieUnauthorized;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth'       => \Modules\Admin\Http\Middleware\Authenticate::class,
            'jwt.auth'   => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh'=> \PHPOpenSourceSaver\JWTAuth\Http\Middleware\RefreshToken::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage() ?: 'Unauthenticated.'], 401);
            }
            return null;
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], $e->status);
            }
            return null;
        });

        $exceptions->render(function (SpatieUnauthorized $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message'      => 'Forbidden: missing permission or role.',
                    'permissions'  => method_exists($e, 'getRequiredPermissions') ? $e->getRequiredPermissions() : null,
                    'roles'        => method_exists($e, 'getRequiredRoles') ? $e->getRequiredRoles() : null,
                    'guards'       => method_exists($e, 'guards') ? $e->guards() : null,
                ], 403);
            }
            return null;
        });

        // 403 - autorização do Laravel (policies/gates)
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Forbidden: not authorized.',
                    'detail'  => $e->getMessage(),
                ], 403);
            }
            return null;
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && $e instanceof HttpExceptionInterface) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'HTTP error',
                ], $e->getStatusCode(), $e->getHeaders());
            }
            return null;
        });
    })->create();
