<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class AdminProductController extends Controller
{
    // Listar productos
    public function index($storeId)
    {
        $products = Product::where('store_id', $storeId)->get();

        return response()->json($products);
    }

    // Crear producto
    public function store(Request $request, $storeId)
    {
        $product = Product::create([
            'store_id' => $storeId,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'active' => true,
        ]);

        return response()->json($product, 201);
    }

    // Ver producto individual
    public function show($storeId, $productId)
    {
        $product = Product::where('store_id', $storeId)
            ->findOrFail($productId);

        return response()->json($product);
    }

    // Actualizar producto
    public function update(Request $request, $storeId, $productId)
    {
        $product = Product::where('store_id', $storeId)
            ->findOrFail($productId);

        $product->update($request->only([
            'name',
            'description',
            'price',
            'stock',
            'active'
        ]));

        return response()->json($product);
    }

    // Eliminar producto
    public function destroy($storeId, $productId)
    {
        $product = Product::where('store_id', $storeId)
            ->findOrFail($productId);

        $product->delete();

        return response()->json([
            'message' => 'Product deleted'
        ]);
    }
}
