<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Crear un carrito para una tienda
     */
    public function create(int $storeId): JsonResponse
    {
        $cart = Cart::create([
            'store_id' => $storeId,
            'token' => Str::uuid()->toString(),
        ]);

        return response()->json([
            'token' => $cart->token,
        ], 201);
    }

    /**
     * Ver carrito (por tienda + token)
     * GET /api/stores/{storeId}/cart?token=xxx
     */
public function show(Request $request, int $storeId): JsonResponse
{
    $token = $request->query('token');

    $cart = Cart::where('store_id', $storeId)
        ->where('token', $token)
        ->with('items.product')
        ->firstOrFail();

    $items = $cart->items->map(function ($item) {   
        $subtotal = (float) $item->price * $item->quantity;

        return [
            'product_id' => $item->product_id,
            'name'       => $item->product->name,
            'price'      => (float) $item->price,   // snapshot
            'quantity'   => $item->quantity,
            'subtotal'   => $subtotal,
        ];
    });

    return response()->json([
        'cart' => [
            'token'       => $cart->token,
            'items_count' => $items->sum('quantity'),
            'subtotal'    => $items->sum('subtotal'),
            'items'       => $items,
        ],
    ]);
}



    /**
     * Agregar producto al carrito
     * POST /api/stores/{storeId}/cart/items?token=xxx
     */
    public function addItem(Request $request, string $token): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $cart = Cart::where('token', $token)->firstOrFail();

        $product = Product::where('id', $data['product_id'])
            ->where('store_id', $cart->store_id)
            ->where('active', true)
            ->firstOrFail();

        // Item existente en carrito
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $currentQty = $existingItem ? $existingItem->quantity : 0;
        $newQty = $currentQty + $data['quantity'];

        // Validar stock
        if ($newQty > $product->stock) {
            return response()->json([
                'message' => 'Not enough stock available',
                'stock_available' => $product->stock,
            ], 422);
        }

        CartItem::updateOrCreate(
            [
                'cart_id'   => $cart->id,
                'product_id'=> $product->id,
            ],
            [
                'quantity' => $newQty,
                'price'    => $product->price,
            ]
        );

        return response()->json([
            'message' => 'Product added to cart',
        ]);
}


    public function removeItem(Request $request, int $storeId, int $itemId): JsonResponse
    {
        $token = $request->query('token');

        $cart = Cart::where('store_id', $storeId)
            ->where('token', $token)
            ->firstOrFail();

        $item = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'message' => 'Item removed from cart',
        ]);
}

public function decrementItem(string $token, int $itemId): JsonResponse
{
    $cart = Cart::where('token', $token)->firstOrFail();

    $item = CartItem::where('id', $itemId)
        ->where('cart_id', $cart->id)
        ->firstOrFail();

    if ($item->quantity > 1) {
        $item->decrement('quantity');
    } else {
        $item->delete();
    }

    return response()->json([
        'message' => 'Item updated',
    ]);
}


}
