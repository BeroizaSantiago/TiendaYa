<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\User;

class StoreUserController extends Controller
{
    /**
     * Agregar usuario a la tienda (solo owner)
     */
    public function addUser(Request $request, Store $store)
    {
        $currentUser = $request->user();

        // Verificar que el usuario actual sea owner
        $membership = $store->users()
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$membership || $membership->pivot->role !== 'owner') {
            return response()->json([
                'message' => 'Only the owner can add users to this store.'
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:editor'
        ]);

        $userToAdd = User::where('email', $validated['email'])->first();

        // Evitar duplicados
        if ($store->users()->where('user_id', $userToAdd->id)->exists()) {
            return response()->json([
                'message' => 'User already belongs to this store.'
            ], 422);
        }

        $store->users()->attach($userToAdd->id, [
            'role' => $validated['role']
        ]);

        return response()->json([
            'message' => 'User added successfully.'
        ]);
    }

    /**
     * Eliminar usuario de la tienda (solo owner)
     */
    public function removeUser(Request $request, Store $store, User $user)
    {
        $currentUser = $request->user();

        $membership = $store->users()
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$membership || $membership->pivot->role !== 'owner') {
            return response()->json([
                'message' => 'Only the owner can remove users from this store.'
            ], 403);
        }

        // No permitir eliminar al owner
        $targetMembership = $store->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$targetMembership) {
            return response()->json([
                'message' => 'User does not belong to this store.'
            ], 404);
        }

        if ($targetMembership->pivot->role === 'owner') {
            return response()->json([
                'message' => 'Owner cannot be removed.'
            ], 422);
        }

        $store->users()->detach($user->id);

        return response()->json([
            'message' => 'User removed successfully.'
        ]);
    }

    /**
     * Listar miembros de la tienda
     */
    public function listUsers(Request $request, Store $store)
    {
        $currentUser = $request->user();

        $membership = $store->users()
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$membership) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $users = $store->users()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role
            ];
        });

        return response()->json($users);
    }
}