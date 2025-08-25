<?php

namespace Modules\Admin\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as BaseAuthenticate;

class Authenticate extends BaseAuthenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');

            $auth = $request->header('Authorization', '');
            if (!preg_match('/^Bearer\s+\S+$/i', $auth)) {
                return response()->json([
                    'message' => 'Token de autenticação ausente ou inválido. Use Authorization: Bearer <token>.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        return parent::handle($request, $next, ...$guards);
    }

    protected function redirectTo($request): ?string
    {
        if ($request->is('api/*')) {
            return null;
        }

        if (! $request->expectsJson()) {
            return route('login');
        }

        return null;
    }
}
