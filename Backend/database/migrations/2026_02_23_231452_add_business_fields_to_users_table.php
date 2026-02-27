<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('emprendedor')->after('email');
        $table->string('phone')->nullable()->after('role');
        $table->string('address')->nullable()->after('phone');
        $table->string('cuit')->nullable()->after('address');
        $table->string('business_name')->nullable()->after('cuit');
        $table->string('status')->nullable()->after('business_name');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
