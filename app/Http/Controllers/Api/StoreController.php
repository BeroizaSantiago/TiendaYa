<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;

class StoreController extends Controller
{
    public function index()
    {
        return response()->json([
            'stores' => Store::all()
        ]);
    }
}
