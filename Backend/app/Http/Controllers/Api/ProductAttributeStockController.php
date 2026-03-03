<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAttributeStock;
use App\Models\User;
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

        /** @var User $user */
        $user = Auth::user();

        $storeId = $request->header('X-Store-Id');

        if (!$storeId) {
            abort(400, 'X-Store-Id header is required');
        }

        $store = $user->stores()
            ->where('stores.id', $storeId)
            ->first();

        if (!$store) {
            abort(404, 'Store not found for this user');
        }

        $product = Product::findOrFail($validated['product_id']);

        // Validar que el producto pertenezca a la tienda activa
        if ($product->store_id != $store->id) {
            abort(403, 'Unauthorized');
        }

        return response()->json(
            ProductAttributeStock::create($validated),
            201
        );
    }
}