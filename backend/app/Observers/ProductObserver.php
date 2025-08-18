<?php

namespace App\Observers;

use App\Models\Product;
use App\Service\UserService;

class ProductObserver
{
    public function __construct(protected UserService $userService) {}

    public function created(Product $product): void   { $this->userService->flushUsersStatsCache(); }
    public function updated(Product $product): void   { $this->userService->flushUsersStatsCache(); }
    public function deleted(Product $product): void   { $this->userService->flushUsersStatsCache(); }
}
