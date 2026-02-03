<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\Admin\AdminProductController;


Route::prefix('admin/stores/{store}')->group(function () {
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::get('/products/{product}', [AdminProductController::class, 'show']);
    Route::put('/products/{product}', [AdminProductController::class, 'update']);
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);
});

Route::get('/stores', [StoreController::class, 'index']);

Route::get('/stores/{storeId}/products', [ProductController::class, 'index']);

Route::get(
    '/stores/{storeId}/products/{productId}',
    [ProductController::class, 'show']
);

Route::post(
    '/stores/{storeId}/cart',
    [CartController::class, 'create']
);

Route::get(
    '/stores/{storeId}/cart',
    [CartController::class, 'show']
    
);

Route::post(
    '/stores/{storeId}/cart/items',
    [CartController::class, 'addItem']
);

Route::delete(
    '/stores/{storeId}/cart/items/{itemId}',
    [CartController::class, 'removeItem']
);

Route::patch(
    '/cart/{token}/items/{itemId}/decrement',
    [CartController::class, 'decrementItem']
);


Route::post(
    '/stores/{storeId}/cart/items',
    [CartItemController::class, 'store']
);

Route::post(
    '/stores/{storeId}/checkout',
    [CheckoutController::class, 'checkout']
);

Route::post(
    '/orders/{orderId}/status',
    [CheckoutController::class, 'updateStatus']
);

Route::get(
    '/stores/{storeId}/orders',
    [OrderController::class, 'index']
);

Route::post(
    '/stores/{storeId}/orders/{orderId}/cancel',
    [OrderController::class, 'cancel']
);

Route::patch(
    'stores/{store}/orders/{order}/status',
    [OrderController::class, 'updateStatus']
);

Route::get(
    '/stores/{store}/orders/{order}', 
    [OrderController::class, 'show']
);

Route::get(
    '/stores/{store}/orders/{order}/history',
    [OrderController::class, 'history']
);