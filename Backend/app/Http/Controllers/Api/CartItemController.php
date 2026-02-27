<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ProductAttributeStock;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{


public function store(Request $request, int $storeId): JsonResponse
{
    $data = $request->validate([
        'token' => 'required|string',
        'product_id' => 'required|integer',
        'attribute_value_id' => 'required|integer|exists:attribute_values,id',
        'quantity' => 'required|integer|min:1',
    ]);

    return DB::transaction(function () use ($data, $storeId) {

        $cart = Cart::where('store_id', $storeId)
            ->where('token', $data['token'])
            ->firstOrFail();

        $product = Product::where('id', $data['product_id'])
            ->where('store_id', $storeId)
            ->where('active', true)
            ->firstOrFail();

        $productStock = ProductAttributeStock::where('product_id', $product->id)
            ->where('attribute_value_id', $data['attribute_value_id'])
            ->lockForUpdate()
            ->firstOrFail();

        // Buscar item existente (misma variante)
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('attribute_value_id', $data['attribute_value_id'])
            ->first();

        $currentQty = $item ? $item->quantity : 0;
        $newQty = $currentQty + $data['quantity'];

        if ($newQty > $productStock->stock) {
            return response()->json([
                'message' => 'Not enough stock for this variant',
                'stock_available' => $productStock->stock,
            ], 422);
        }

        if ($item) {
            $item->quantity = $newQty;
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'attribute_value_id' => $data['attribute_value_id'],
                'quantity' => $data['quantity'],
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'message' => 'Producto variante agregado al carrito',
            'item' => $item,
        ], 201);
    });
}
}
