<?php

namespace App\Observers;

use App\Models\User;
use App\Service\UserService;

class UserObserver
{
    public function __construct(protected UserService $userService) {}

    public function created(User $user): void   { $this->userService->flushUsersStatsCache(); }
    public function updated(User $user): void   { $this->userService->flushUsersStatsCache(); }
    public function deleted(User $user): void   { $this->userService->flushUsersStatsCache(); }
}
