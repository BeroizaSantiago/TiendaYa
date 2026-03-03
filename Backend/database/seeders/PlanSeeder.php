<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'name' => 'Emprendedor',
            'max_products' => 30,
            'commission_rate' => 0.015,
        ]);

        Plan::create([
            'name' => 'PyME',
            'max_products' => null,
            'commission_rate' => 0.008,
            'bulk_upload' => true,
            'advanced_reports' => true,
            'advanced_coupons' => true,
            'automation_level' => 'limited',
            'support_level' => 'medium'
        ]);

        Plan::create([
            'name' => 'Empresa',
            'max_products' => null,
            'commission_rate' => 0,
            'bulk_upload' => true,
            'advanced_reports' => true,
            'advanced_coupons' => true,
            'b2b_enabled' => true,
            'custom_integrations' => true,
            'automation_level' => 'full',
            'support_level' => 'high'
        ]);
    }
}
