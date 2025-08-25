<?php

namespace Modules\Admin\Repositories;

use App\Repository\BaseRepository;
use Modules\Admin\Models\Menu;

class MenuRepository extends BaseRepository
    {
        public function __construct(Menu $model)
        {
            parent::__construct($model);
        }

        public function getActiveRootsWithChildren()
        {
            return $this->model->newQuery()
                ->select(['id','title','route','icon','permission','parent_id','sort','active'])
                ->whereNull('parent_id')
                ->where('active', true)
                ->with(['children' => function ($q) {
                    $q->select(['id','title','route','icon','permission','parent_id','sort','active'])
                        ->where('active', true)
                        ->orderBy('sort');
                }])
                ->orderBy('sort')
                ->get();
        }
    }
