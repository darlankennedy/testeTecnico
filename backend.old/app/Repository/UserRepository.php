<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository
{
    protected array $searchable = ['name','email','cpf','company.name'];
    protected array $filterable = ['name','email','cpf','status','company_id','created_at','company.name'];
    protected array $sortable   = ['id','name','email','created_at'];
    public function __construct(User $model){
        parent::__construct($model);
    }


    /**
     * Get all users that have at least one product.
     * Returns: id, name, email, products_count, products_total_value
     *
     * @return
     */
    public function selectUsersWithProducts()
    {
        $sql = "
            SELECT
                u.id,
                u.name,
                u.email,
                COUNT(p.id) AS products_count,
                COALESCE(SUM(p.price), 0) AS products_total_value
            FROM users u
            INNER JOIN products p ON p.user_id = u.id
            GROUP BY u.id, u.name, u.email
            HAVING COUNT(p.id) > 0
            ORDER BY u.name ASC
        ";

        return collect(DB::select($sql));
    }

    /**
     * Return users that do NOT have any products.
     *
     * @return \Illuminate\Support\Collection
     */
    public function selectUsersWithoutProducts()
    {
        $sql = "
        SELECT
            u.id,
            u.name,
            u.email
        FROM users u
        LEFT JOIN products p ON p.user_id = u.id
        WHERE p.id IS NULL
        ORDER BY u.name ASC
    ";

        return collect(DB::select($sql));
    }

    /**
     * Return users count.
     *
     * @return int
     */
    public function countAll(): int
    {
        return (int) DB::table('users')->count();
    }


}
