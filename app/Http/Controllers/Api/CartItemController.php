<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartItemController extends Controller
{
    public function store(Request $request, int $storeId): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string',
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('store_id', $storeId)
            ->where('token', $data['token'])
            ->firstOrFail();

        $product = Product::where('id', $data['product_id'])
            ->where('store_id', $storeId)
            ->where('active', true)
            ->firstOrFail();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->quantity += $data['quantity'];
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'message' => 'Producto agregado al carrito',
            'item' => $item,
        ], 201);
    }
}
