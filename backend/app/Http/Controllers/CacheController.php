<?php

namespace App\Http\Controllers;

use App\Service\UserService;
use Illuminate\Http\JsonResponse;

class CacheController extends Controller
{
    /**
     * Flush users stats cache.
     *
     * Limpa o cache de estatísticas de usuários (ex.: usersWithProducts / usersWithoutProducts).
     * @group Maintenance
     * @authenticated
     *
     * @response 200 {"status":"ok"}
     */
    public function flush(UserService $svc): JsonResponse
    {
        $svc->flushUsersStatsCache();
        return response()->json(['status' => 'ok']);
    }
}
