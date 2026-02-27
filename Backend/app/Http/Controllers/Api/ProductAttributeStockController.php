<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAttributeStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProductAttributeStockController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'attribute_value_id' => 'required|exists:attribute_values,id',
            'stock' => 'required|integer|min:0'
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Seguridad básica: validar que el producto sea de la store del usuario
        if ($request->user()->store->id !== $product->store_id) {
            abort(403, 'Unauthorized');
        }

        return response()->json(
            ProductAttributeStock::create($validated),
            201
        );
    }


}