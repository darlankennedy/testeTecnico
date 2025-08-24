<?php

namespace Tests\Service;

use App\Models\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use PhpParser\Builder;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery;

class UserServiceTest extends TestCase
{
    protected $userRepositoryMock;
    protected $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->userService = new UserService($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testAllReturnsArrayOfUsers()
    {
        $users = collect([new User(['id' => 1, 'name' => 'John Doe'])]);

        $this->userRepositoryMock
            ->shouldReceive('all')
            ->once()
            ->andReturn($users);

        $result = $this->userService->all();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('John Doe', $result[0]['name']);
    }

    public function testFindReturnsUser()
    {
        $user = new User(['id' => 1, 'name' => 'John Doe']);

        $this->userRepositoryMock
            ->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($user);

        $result = $this->userService->find(1);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->name);
    }

    public function testCreateReturnsUser()
    {
        $data = ['name' => 'John Doe'];
        $user = new User($data);

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($user);

        $result = $this->userService->create($data);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->name);
    }

    public function testUpdateReturnsUpdatedUser()
    {
        $data = ['name' => 'Jane Doe'];
        $user = new User($data);

        $this->userRepositoryMock
            ->shouldReceive('update')
            ->with(1, $data)
            ->once()
            ->andReturn($user);

        $result = $this->userService->update(1, $data);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Jane Doe', $result->name);
    }


    public function testDeleteReturnsTrue()
    {
        $this->userRepositoryMock
            ->shouldReceive('delete')
            ->with(1)
            ->once()
            ->andReturn(true);

        $result = $this->userService->delete(1);
        $this->assertTrue($result);
    }

    public function testExistsReturnsBoolean()
    {
        $this->userRepositoryMock
            ->shouldReceive('exists')
            ->with(['email' => 'john@example.com'])
            ->once()
            ->andReturn(true);

        $result = $this->userService->exists(['email' => 'john@example.com']);
        $this->assertTrue($result);
    }

    public function testPaginateReturnsArray()
    {
        $users = collect([new User(['id' => 1, 'name' => 'John Doe'])]);

        $this->userRepositoryMock
            ->shouldReceive('paginate')
            ->with(15, [], 'id', 'asc')
            ->once()
            ->andReturn($users);

        $result = $this->userService->paginate();
        $this->assertIsArray($result);
        $this->assertEquals('John Doe', $result[0]['name']);
    }

    public function testQueryReturnsBuilder()
    {
        $builder = Mockery::mock(Builder::class);

        $this->userRepositoryMock
            ->shouldReceive('query')
            ->once()
            ->andReturn($builder);

        $result = $this->userService->query();
        $this->assertInstanceOf(Builder::class, $result);
    }

}
