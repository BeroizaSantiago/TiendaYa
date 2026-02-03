<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(int $storeId): JsonResponse
    {
        $store = Store::findOrFail($storeId);

        return response()->json([
            'store_id' => $store->id,
            'products' => ProductResource::collection(
                $store->products()->where('active', true)->get()
            ),
        ]);
    }

    public function show(int $storeId, int $productId): JsonResponse
    {
        $product = Product::where('store_id', $storeId)
            ->where('id', $productId)
            ->where('active', true)
            ->firstOrFail();

        return response()->json(
            new ProductResource($product)
        );
    }
}
