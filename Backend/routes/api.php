<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AttributeController;
use App\Http\Controllers\Api\AttributeValueController;
use App\Http\Controllers\Api\ProductAttributeStockController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\DeliveryNoteController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\StoreUserController;
use App\Http\Controllers\Api\StoreMemberController;


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->group(function () {});
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class)
        ->only(['index', 'store']);
    Route::apiResource('attributes', AttributeController::class)
        ->only(['index', 'store']);
    Route::post('attribute-values', [AttributeValueController::class, 'store']);

    Route::post('product-attribute-stock', [ProductAttributeStockController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user()->load('stores');
    });

    Route::get('/users', [UserController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->group(function () {
        Route::apiResource('products', AdminProductController::class);
    });
});

/**
 * Ruta para obtener el plan del usuario autenticado
 */

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me/plan', [MeController::class, 'plan']);
});

/*
|--------------------------------------------------------------------------
| STORES (Public)
|--------------------------------------------------------------------------
*/

Route::get('/stores', [StoreController::class, 'index']);

/*
|--------------------------------------------------------------------------
| STORES (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/stores', [StoreController::class, 'store']);
});

Route::prefix('stores/{store}')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | CART
    |--------------------------------------------------------------------------
    */

    Route::post('/cart', [CartController::class, 'create']);
    Route::get('/cart', [CartController::class, 'show']);

    Route::prefix('cart')->group(function () {
        Route::post('/items', [CartItemController::class, 'store']);
        Route::delete('/items/{item}', [CartController::class, 'removeItem']);
    });

    /*
    |--------------------------------------------------------------------------
    | CHECKOUT
    |--------------------------------------------------------------------------
    */

    Route::post('/checkout', [CheckoutController::class, 'checkout']);

    /*
    |--------------------------------------------------------------------------
    | ORDERS
    |--------------------------------------------------------------------------
    */

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/orders/{order}/history', [OrderController::class, 'history']);
    Route::get('/orders/{order}/shipment', [OrderController::class, 'shipment']);
    Route::get('/orders/{order}/tracking', [OrderController::class, 'tracking']);

    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
});


/*
|--------------------------------------------------------------------------
| STORE USERS
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/stores/{store}/users', [StoreUserController::class, 'listUsers']);
    Route::post('/stores/{store}/users', [StoreUserController::class, 'addUser']);
    Route::delete('/stores/{store}/users/{user}', [StoreUserController::class, 'removeUser']);

});



/*
|--------------------------------------------------------------------------
| GLOBAL CART ACTIONS
|--------------------------------------------------------------------------
*/

Route::patch('/cart/{token}/items/{item}/decrement', [CartController::class, 'decrementItem']);


/*|--------------------------------------------------------------------------
| INVOICES & DELIVERY NOTES 
|--------------------------------------------------------------------------
*/
Route::post('orders/{order}/invoice', [InvoiceController::class, 'issue']);
Route::post('orders/{order}/delivery-note', [DeliveryNoteController::class, 'issue']);


/*
|--------------------------------------------------------------------------
| STORE MEMBERS (OWNER ONLY)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post(
        '/stores/{store}/members',
        [StoreMemberController::class, 'addMember']
    )->middleware('store.role:owner');

    Route::delete(
        '/stores/{store}/members/{userId}',
        [StoreMemberController::class, 'removeMember']
    )->middleware('store.role:owner');
});