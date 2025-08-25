<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Admin\Services\MenuService;

class MenuController extends Controller
{
    public function __construct(private MenuService $service) {}

    public function index()
    {
        $user = auth('api')->user();
        abort_if(!$user, 401, 'Unauthorized');

        return response()->json($this->service->forUser($user));
    }

    public function refresh()
    {
        $user = auth('api')->user();
        abort_if(!$user, 401, 'Unauthorized');

        return response()->json($this->service->warmForUser($user));
    }
}

