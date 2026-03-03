<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;

class CheckStoreRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $storeId = $request->route('store');
        $user = $request->user();

        $store = Store::findOrFail($storeId);

        $membership = $store->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership || !in_array($membership->pivot->role, $roles)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return $next($request);
    }
}