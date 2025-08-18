<?php

namespace Tests\Service;

use App\Models\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use PHPUnit\Framework\TestCase;
use Mockery;

class ProductServiceTest extends TestCase
{
    protected $productRepositoryMock;
    protected $productService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepositoryMock = \Mockery::mock(ProductRepository::class);
        $this->productService = new ProductService($this->productRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testFindReturnsProduct()
    {

        $expectedProduct = new Product([
            'id' => 1,
            'name' => 'product1',
            'price' => 100,
            'description' => 'description1',
            'user_id' => 1,
        ]);

        $this->productRepositoryMock
            ->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($expectedProduct);

        $result = $this->productService->find(1);

        $this->assertEquals($expectedProduct, $result);

    }

    public function testCreateProduct()
    {
        $data = ['name' => 'New Product', 'price' => 100, 'description' => 'description'];

        $product = new Product($data);

        $this->productRepositoryMock
            ->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($product);

        $result = $this->productService->create($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('New Product', $result->name);
    }

    /**
     * @test
     */
    public function testAllReturnsArrayOfUsers()
    {
        $products = collect([new Product(['id' => 1, 'name' => 'prod', 'price' => 100, 'description' => 'description1'])]);

        $this->productRepositoryMock
            ->shouldReceive('all')
            ->once()
            ->andReturn($products);

        $result = $this->productService->all();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('prod', $result[0]['name']);
    }

    public function testUpdateProduct()
    {
        $product = new Product(['id' => 1, 'name' => 'Old Name']);
        $data = ['name' => 'Updated Name'];

        $this->productRepositoryMock
            ->shouldReceive('update')
            ->with(1, $data)
            ->once()
            ->andReturn(new Product(array_merge($product->toArray(), $data)));

        $result = $this->productService->update(1, $data);

        $this->assertEquals('Updated Name', $result->name);
    }

    public function testDeleteProduct()
    {
        $this->productRepositoryMock
            ->shouldReceive('delete')
            ->with(1)
            ->once()
            ->andReturnTrue();

        $result = $this->productService->delete(1);

        $this->assertTrue($result);
    }
}
