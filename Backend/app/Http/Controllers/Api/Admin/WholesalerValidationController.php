<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ValidationHistory;

class WholesalerValidationController extends Controller
{
        public function approve($userId)
    {
        $user = User::findOrFail($userId);

        $user->update([
            'status' => 'active'
        ]);

        ValidationHistory::where('user_id', $user->id)
            ->latest()
            ->update([
                'validation_status' => 'approved',
                'validated_at' => now()
            ]);

        return response()->json([
            'message' => 'Wholesaler approved'
        ]);
    }

    public function reject(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $user->update([
            'status' => 'rejected'
        ]);

        ValidationHistory::where('user_id', $user->id)
            ->latest()
            ->update([
                'validation_status' => 'rejected',
                'observations' => $request->observations,
                'validated_at' => now()
            ]);

        return response()->json([
            'message' => 'Wholesaler rejected'
        ]);
    }
}
