<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class AdminProductController extends Controller
{
    protected function getStore()
    {
        $store = Auth::user()->store;

        if (!$store) {
            abort(404, 'Store not found for this user');
        }

        return $store;
    }

    // Listar productos
    public function index()
    {
        $store = $this->getStore();

        return response()->json(
        $store->products()
        ->with('attributeStocks.attributeValue.attribute')
        ->latest()
        ->get()
);
    }

    // Crear producto
    public function store(Request $request)
    {
        $store = $this->getStore();

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = $store->products()->create([
            ...$validated,
            'active' => true
        ]);

        return response()->json($product, 201);
    }

    // Ver producto individual
    public function show($productId)
    {
        $store = $this->getStore();

        $product = $store->products()
            ->with('attributeStocks.attributeValue.attribute')
            ->where('id', $productId)
            ->firstOrFail();

        return response()->json($product);
    }

    // Actualizar producto
    public function update(Request $request, $productId)
    {
        $store = $this->getStore();

        $product = $store->products()
            ->where('id', $productId)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'active' => 'sometimes|boolean'
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    // Eliminar producto
    public function destroy($productId)
    {
        $store = $this->getStore();

        $product = $store->products()
            ->where('id', $productId)
            ->firstOrFail();

        $product->delete();

        return response()->json([
            'message' => 'Product deleted'
        ]);
    }
}