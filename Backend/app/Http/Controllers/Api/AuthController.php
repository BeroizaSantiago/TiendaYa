<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:emprendedor,pyme,mayorista',
            'store_name' => 'required|string|max:255'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], 
            'role' => $validated['role'],
            'status' => $validated['role'] === 'mayorista' ? 'pending' : 'active'
        ]);

        $store = Store::create([
            'user_id' => $user->id,
            'name' => $validated['store_name'],
            'slug' => Str::slug($validated['store_name']) . '-' . uniqid(),
            'active' => true
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'store' => $store,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Cuenta pendiente de aprobación'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        

        return response()->json([
            'user' => $user,
            'store' => $user->store,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout correcto'
        ]);
    }
}