<?php

namespace Modules\Admin\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Repositories\MenuRepository;

class MenuService
{
    protected MenuRepository $repository;

    public function __construct(MenuRepository $repository)
    {
        $this->repository = $repository;
    }

    /** Menu do usuário logado (cache forever com chave versionada) */
    public function forUser(Authenticatable $user, bool $forceRefresh = false): array
    {
        $key = $this->keyForUser($user);

        if ($forceRefresh) {
            Cache::forget($key);
        }

        return Cache::rememberForever($key, function () use ($user) {
            return $this->buildForUser($user);
        });
    }

    /** Reconstrói e reescreve o cache do usuário agora */
    public function warmForUser(Authenticatable $user): array
    {
        Cache::forget($this->keyForUser($user));
        return $this->forUser($user);
    }

    /** Invalida só o usuário (quando mudar role/permissão dele) */
    public function invalidateForUser(int|string $userId): void
    {
        Cache::forget("menu:v{$this->currentVersion()}:user:{$userId}");
    }

    /** Invalidação global (quando o menu em si muda) */
    public function bumpVersion(): int
    {
        $next = (int) Cache::increment('menu:version');
        if ($next === 1) { // drivers sem a chave ainda
            Cache::forever('menu:version', 2);
            $next = 2;
        }
        return $next;
    }

    public function currentVersion(): int
    {
        return (int) (Cache::get('menu:version', 1));
    }


    protected function keyForUser(Authenticatable $user): string
    {
        return "menu:v{$this->currentVersion()}:user:{$user->getAuthIdentifier()}";
    }

    protected function buildForUser(Authenticatable $user): array
    {
        $permissionNames = method_exists($user, 'getAllPermissions')
            ? $user->getAllPermissions()->pluck('name')->all()
            : [];

        // pega a ÁRVORE diretamente do repository
        $roots = $this->repository->getActiveRootsWithChildren();

        $filtered = $roots->filter(function ($item) use ($permissionNames) {
            $allowed = is_null($item->permission) || in_array($item->permission, $permissionNames, true);
            if (!$allowed) return false;

            $children = collect($item->children)->filter(function ($child) use ($permissionNames) {
                return is_null($child->permission) || in_array($child->permission, $permissionNames, true);
            })->values();

            if (is_null($item->route) && $children->isEmpty()) return false;

            $item->setRelation('children', $children);
            return true;
        })->values();

        return $filtered->map(function ($m) {
            return [
                'id'         => $m->id,
                'title'      => $m->title,
                'route'      => $m->route,
                'icon'       => $m->icon,
                'permission' => $m->permission,
                'sort'       => $m->sort,
                'children'   => collect($m->children)->map(fn ($c) => [
                    'id'         => $c->id,
                    'title'      => $c->title,
                    'route'      => $c->route,
                    'icon'       => $c->icon,
                    'permission' => $c->permission,
                    'sort'       => $c->sort,
                ])->all(),
            ];
        })->all();
    }
}
