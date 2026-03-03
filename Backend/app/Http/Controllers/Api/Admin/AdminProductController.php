<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminProductController extends Controller
{
    /**
     * Resolver la tienda desde el header X-Store-Id
     * y verificar que pertenezca al usuario autenticado
     */
    protected function getStore(Request $request)
    {
        $storeId = $request->header('X-Store-Id');

        if (!$storeId) {
            abort(400, 'X-Store-Id header is required');
        }

        /** @var User $user */
        $user = Auth::user();

        $store = $user->stores()
            ->with('plan')
            ->where('stores.id', $storeId)
            ->first();

        if (!$store) {
            abort(404, 'Store not found for this user');
        }

        return $store;
    }

    /**
     * Verificar rol (owner o editor)
     */
    protected function checkRole($store)
    {
        $membership = $store->users()
            ->where('users.id', Auth::id())
            ->first();

        if (!$membership || !in_array($membership->pivot->role, ['owner', 'editor'])) {
            abort(403, 'Unauthorized');
        }
    }

    // Listar productos
    public function index(Request $request)
    {
        $store = $this->getStore($request);

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
        $store = $this->getStore($request);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Verificar rol
        $this->checkRole($store);

        // Obtener plan desde la tienda (NO desde user)
        $plan = $store->plan;

        // Validar límite de productos según plan
        if ($plan && $plan->max_products !== null) {

            $count = $store->products()->count();

            if ($count >= $plan->max_products) {
                abort(403, 'Product limit reached for your plan');
            }
        }

        $product = $store->products()->create([
            ...$validated,
            'active' => true
        ]);

        return response()->json($product, 201);
    }

    // Ver producto individual
    public function show(Request $request, $productId)
    {
        $store = $this->getStore($request);

        $product = $store->products()
            ->with('attributeStocks.attributeValue.attribute')
            ->where('id', $productId)
            ->firstOrFail();

        return response()->json($product);
    }

    // Actualizar producto
    public function update(Request $request, $productId)
    {
        $store = $this->getStore($request);

        // Verificar rol
        $this->checkRole($store);

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
    public function destroy(Request $request, $productId)
    {
        $store = $this->getStore($request);

        // Verificar rol
        $this->checkRole($store);

        $product = $store->products()
            ->where('id', $productId)
            ->firstOrFail();

        $product->delete();

        return response()->json([
            'message' => 'Product deleted'
        ]);
    }
}