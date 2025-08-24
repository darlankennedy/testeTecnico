<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Service\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage   = (int) $request->integer('per_page', 10);
            $orderBy   = (string) $request->get('order_by', 'id');
            $direction = (string) $request->get('direction', 'asc');

            $filters = $request->only(['search']);
            $result = $this->productService->paginate($perPage, $filters, $orderBy, $direction);

            return response()->json([
                'data' => $result['data'] ?? [],
                'meta' => [
                    'total'        => $result['total'] ?? 0,
                    'per_page'     => $result['per_page'] ?? $perPage,
                    'current_page' => $result['current_page'] ?? (int) $request->integer('page', 1),
                    'last_page'    => $result['last_page'] ?? 1,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Unable to paginate users.'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     * (Se for API, normalmente não usamos)
     */
    public function create()
    {
        return response()->json(['message' => 'Not needed for API'], 200);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    public function store(StoreProductRequest $request): JsonResponse
    {

        $data = $request->validated();
        $product = $this->productService->create($data);

        if (!$product) {
            return response()->json(['message' => 'Failed to create product'], 500);
        }

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     * (Se for API, normalmente não usamos)
     */
    public function edit(string $id)
    {
        return response()->json(['message' => 'Not needed for API'], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $updated = $this->productService->update($id, $data);

        if (!$updated) {
            return response()->json(['message' => 'Failed to update product'], 500);
        }

        return response()->json(['message' => 'Product updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->productService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete product'], 500);
        }

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function count(): JsonResponse
    {
        try {
            return response()->json($this->productService->productsCount());
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Unable to fetch count.'], 500);
        }
    }


}
