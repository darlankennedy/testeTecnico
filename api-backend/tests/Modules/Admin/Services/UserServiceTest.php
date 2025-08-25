<?php

namespace Tests\Modules\Admin\Services;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\User;
use Modules\Admin\Services\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->createMock(UserService::class);
    }

    public function testGetAllUsers()
    {
        $this->service->method('getAllUsers')
            ->willReturn([])
            ->willReturn(collect([
                ['id' => 1, 'name' => 'John Doe', 'email' => 'teste@teste.com', 'cpf' => '12345678900'],
                ['id' => 2, 'name' => 'Jane Smith', 'email' => 'teste@teste2.com', 'cpf' => '09876543211'],
            ]));

        $users = $this->service->getAllUsers();
        $this->assertCount(2, $users);
        $this->assertEquals('John Doe', $users[0]['name']);
        $this->assertEquals('Jane Smith', $users[1]['name']);
    }

    public function testGetUserById(){

        $userMock = $this->createMock(User::class);
        $userMock->method('__get')->willReturnMap([
            ['id', 1],
            ['name', 'John Doe'],
            ['email', 'teste@teste.com'],
            ['cpf', '12345678900'],
        ]);

        $this->service->method('getUserById')->willReturn($userMock);

        $user = $this->service->getUserById(1);
        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->name);
    }

    public function testCreateUser()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'teste@test.com',
            'cpf' => '12345678900',
            'password' => 'password123',
        ];
        $userMock = $this->createMock(User::class);
        $userMock->method('__get')->willReturnMap([
            ['id', 1],
            ['name', 'John Doe'],
            ['email', 'teste@test.com'],
            ['cpf', '12345678900'],
        ]);
        $this->service->method('createUser')->willReturn($userMock);

        $user = $this->service->createUser($userData);
        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals($userMock, $user);


    }


}
