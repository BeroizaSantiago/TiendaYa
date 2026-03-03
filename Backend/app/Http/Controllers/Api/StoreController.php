<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function index()
    {
        return response()->json([
            'stores' => Store::all()
        ]);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:stores,name',
        'plan_id' => 'required|exists:plans,id'
    ]);

    $store = Store::create([
        'name' => $validated['name'],
        'slug' => Str::slug($validated['name']) . '-' . uniqid(),
        'plan_id' => $validated['plan_id'],
        'active' => true
    ]);

    $store->users()->attach($request->user()->id, [
        'role' => 'owner'
    ]);

    return response()->json([
        'message' => 'Store created successfully',
        'store' => $store->load('plan')
    ], 201);
}
    
}
