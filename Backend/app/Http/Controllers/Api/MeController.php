<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function plan(Request $request)
    {
        $user = $request->user();
        $plan = $user->plan;

        $store = $user->store;

        $productCount = $store
            ? $store->products()->count()
            : 0;

        $remainingSlots = null;

        if ($plan->max_products !== null) {
            $remainingSlots = $plan->max_products - $productCount;
        }

        return response()->json([
            'name' => $plan->name,
            'max_products' => $plan->max_products,
            'current_products' => $productCount,
            'remaining_product_slots' => $remainingSlots,
            'commission_rate' => $plan->commission_rate,
            'bulk_upload' => $plan->bulk_upload,
            'advanced_reports' => $plan->advanced_reports,
            'advanced_coupons' => $plan->advanced_coupons,
            'b2b_enabled' => $plan->b2b_enabled,
            'custom_integrations' => $plan->custom_integrations,
            'automation_level' => $plan->automation_level,
            'support_level' => $plan->support_level
        ]);
    }
}