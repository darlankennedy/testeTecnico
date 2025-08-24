<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $seedUsers = [
            ['name' => 'Admin',        'email' => 'admin@local.test',     'cpf' => '11144477735', 'password' => 'secret123'],
            ['name' => 'Alice Silva',  'email' => 'alice@local.test',     'cpf' => '12345678901', 'password' => 'password'],
            ['name' => 'Bruno Lima',   'email' => 'bruno@local.test',     'cpf' => '98765432100', 'password' => 'password'],
            ['name' => 'Carla Souza',  'email' => 'carla@local.test',     'cpf' => '11122233344', 'password' => 'password'],
            ['name' => 'Diego Santos', 'email' => 'diego@local.test',     'cpf' => '22233344455', 'password' => 'password'],
            ['name' => 'Elaine Reis',  'email' => 'elaine@local.test',    'cpf' => '33344455566', 'password' => 'password'],
            ['name' => 'Fabio Rocha',  'email' => 'fabio@local.test',     'cpf' => '44455566677', 'password' => 'password'],
            ['name' => 'Gabi Pires',   'email' => 'gabi@local.test',      'cpf' => '55566677788', 'password' => 'password'],
            ['name' => 'Hugo Nunes',   'email' => 'hugo@local.test',      'cpf' => '66677788899', 'password' => 'password'],
            ['name' => 'Iara Prado',   'email' => 'iara@local.test',      'cpf' => '77788899900', 'password' => 'password'],
        ];

        foreach ($seedUsers as $u) {
            DB::table('users')->updateOrInsert(
                ['email' => $u['email']], // chave única para upsert
                [
                    'name'              => $u['name'],
                    'cpf'               => $u['cpf'],
                    'email'             => $u['email'],
                    'password'          => Hash::make($u['password']),
                    'email_verified_at' => $now,
                    'remember_token'    => Str::random(10),
                    'updated_at'        => $now,
                    'created_at'        => $now,
                ]
            );
        }
    }
}
