<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\User;

class StoreMemberController extends Controller
{
    // Agregar editor
    public function addMember(Request $request, $store)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:editor'
        ]);

        $store = Store::findOrFail($store);

        $user = User::where('email', $validated['email'])->first();

        // Evitar duplicado
        if ($store->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User already belongs to this store'
            ], 400);
        }

        $store->users()->attach($user->id, [
            'role' => $validated['role']
        ]);

        return response()->json([
            'message' => 'Member added successfully'
        ]);
    }

    // Eliminar miembro
    public function removeMember($store, $userId)
    {
        $store = Store::findOrFail($store);

        $membership = $store->users()
            ->where('user_id', $userId)
            ->first();

        if (!$membership) {
            return response()->json([
                'message' => 'User not in this store'
            ], 404);
        }

        if ($membership->pivot->role === 'owner') {
            return response()->json([
                'message' => 'Cannot remove owner'
            ], 403);
        }

        $store->users()->detach($userId);

        return response()->json([
            'message' => 'Member removed successfully'
        ]);
    }
}