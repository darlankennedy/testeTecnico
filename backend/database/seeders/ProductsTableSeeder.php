<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();


        $userIds = DB::table('users')->pluck('id')->all();
        if (empty($userIds)) {
            $this->call(UsersTableSeeder::class);
            $userIds = DB::table('users')->pluck('id')->all();
        }


        $baseNames = [
            'Notebook 14"', 'Mouse Gamer', 'Teclado Mecânico', 'Monitor 24"', 'Headset USB',
            'Webcam HD', 'Cadeira Escritório', 'SSD 512GB', 'HD Externo 1TB', 'Hub USB-C'
        ];


        for ($i = 1; $i <= 50; $i++) {
            $nameIndex = ($i - 1) % count($baseNames);
            $name = $baseNames[$nameIndex] . " #" . $i;

            $price = mt_rand(1000, 100000) / 100;

            $userId = $userIds[array_rand($userIds)];

            DB::table('products')->insert([
                'name'        => $name,
                'price'       => $price,
                'description' => "Produto seed $i",
                'user_id'     => $userId,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
