<?php

namespace App\Service;

use App\Service\UserService;
use App\Service\ProductService;
use Illuminate\Support\Arr;

class ReportService
{
    public function __construct(
        private readonly UserService $users,
        private readonly ProductService $products,
    ) {}

    /**
     * Monta o resumo do relatório reusando UserService/ProductService.
     *
     * @param array{
     *   dateFrom?: string|null,
     *   dateTo?: string|null,
     *   q?: string|null,
     *   userId?: int|null,
     *   page?: int,
     *   perPage?: int
     * } $filters
     */
    public function summary(array $filters): array
    {
        $page     = max(1, (int)($filters['page']    ?? 1));
        $perPage  = max(1, (int)($filters['perPage'] ?? 10));
        $dateFrom = $filters['dateFrom'] ?? null;
        $dateTo   = $filters['dateTo']   ?? null;
        $q        = $filters['q']        ?? null;
        $userId   = $filters['userId']   ?? null;

        $metrics = [
            'usersTotal'     => (int) $this->users->usersCount(),
            'productsTotal'  => (int) $this->products->productsCount(),
            'usersNoProduct' => (int) count($this->users->usersWithoutProducts()),
        ];

        $repoFilters = array_filter([
            'q'       => $q,
            'user_id' => $userId,
        ], fn ($v) => $v !== null && $v !== '');

        $pageResult = $this->users->paginate($perPage, $repoFilters, 'name', 'asc');

        $rowsRaw = $pageResult['data']  ?? [];
        $total   = (int)($pageResult['total'] ?? count($rowsRaw));

        if ($q || $userId) {
            $rowsRaw = array_values(array_filter($rowsRaw, function (array $u) use ($q, $userId) {
                if ($userId && (int)($u['id'] ?? 0) !== (int)$userId) return false;
                if ($q) {
                    $needle = mb_strtolower($q);
                    $name   = mb_strtolower((string)($u['name']  ?? ''));
                    $email  = mb_strtolower((string)($u['email'] ?? ''));
                    if (!str_contains($name, $needle) && !str_contains($email, $needle)) return false;
                }
                return true;
            }));
            $total = count($rowsRaw);

            $offset = ($page - 1) * $perPage;
            $rowsRaw = array_slice($rowsRaw, $offset, $perPage);
        }

        $topUsers = array_map(function (array $u) use ($dateFrom, $dateTo) {
            $products = (array)($u['products'] ?? []);

            $countAllProducts = count($products); // sem filtro de data

            $sum = 0.0;
            foreach ($products as $p) {
                $createdAt = (string)($p['created_at'] ?? '');
                if ($dateFrom && substr($createdAt, 0, 10) < $dateFrom) continue;
                if ($dateTo   && substr($createdAt, 0, 10) > $dateTo)   continue;

                $price = $p['price'] ?? 0;
                if (is_string($price)) {
                    $price = (float) str_replace(',', '.', $price);
                } else {
                    $price = (float) $price;
                }
                $sum += $price;
            }

            return [
                'id'                    => (int) ($u['id'] ?? 0),
                'name'                  => (string) ($u['name'] ?? ''),
                'products_count'        => (int) $countAllProducts,
                'products_total_value'  => (float) round($sum, 2),
            ];
        }, $rowsRaw);

        $usersSimple = array_map(fn ($uu) => [
            'id' => (int)($uu['id'] ?? 0),
            'name' => (string)($uu['name'] ?? ''),
        ], $this->users->all(['id', 'name']));

        return [
            'metrics'     => $metrics,
            'topUsers'    => $topUsers,
            'pagination'  => ['total' => $total],
            'usersSimple' => $usersSimple,
        ];
    }
}
