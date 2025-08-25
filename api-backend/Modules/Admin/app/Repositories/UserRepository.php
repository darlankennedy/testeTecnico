<?php

namespace Modules\Admin\Repositories;

use App\Repository\BaseRepository;
use Modules\Admin\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}
