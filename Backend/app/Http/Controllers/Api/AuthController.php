<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Plan;
use App\Models\ValidationHistory;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:emprendedor,pyme,mayorista',

            // SOLO si es mayorista
            'phone' => 'required_if:role,mayorista',
            'address' => 'required_if:role,mayorista',
            'cuit' => 'required_if:role,mayorista',
            'business_name' => 'required_if:role,mayorista',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'cuit' => $validated['cuit'] ?? null,
            'business_name' => $validated['business_name'] ?? null,
            'status' => $validated['role'] === 'mayorista' ? 'pending' : 'active'
        ]);

        /*
    Crear historial si es mayorista
    */

        if ($user->role === 'mayorista') {

            ValidationHistory::create([
                'user_id' => $user->id,
                'validation_status' => 'pending'
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
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

        $user = User::with('stores')->find($user->id);
        return response()->json([
            'user' => $user,
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
