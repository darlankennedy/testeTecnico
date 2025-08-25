<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class HealthController extends Controller
{
    public function ping(): JsonResponse
    {
        return response()->json(['pong' => true]);
    }
}
