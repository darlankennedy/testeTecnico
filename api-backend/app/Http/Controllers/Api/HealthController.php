<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * @OA\Info(
 *   title="Minha API",
 *   version="1.0.0"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */
class HealthController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/ping",
     *   tags={"Health"},
     *   summary="Health check",
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="pong", type="boolean", example=true)
     *     )
     *   )
     * )
     */
    public function ping(): JsonResponse
    {
        return response()->json(['pong' => true]);
    }
}
