<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('plans', function (Blueprint $table) {
        $table->id();
        $table->string('name');

        $table->integer('max_products')->nullable();
        $table->decimal('commission_rate', 5, 4)->default(0);

        $table->boolean('bulk_upload')->default(false);
        $table->boolean('advanced_reports')->default(false);
        $table->boolean('advanced_coupons')->default(false);
        $table->boolean('b2b_enabled')->default(false);
        $table->boolean('custom_integrations')->default(false);

        $table->string('automation_level')->default('none'); // none | limited | full
        $table->string('support_level')->default('basic'); // basic | medium | high

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
