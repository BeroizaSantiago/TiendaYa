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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->string('invoice_type'); // A, B, C
        $table->string('invoice_number')->unique();

        $table->decimal('total', 12, 2);

        $table->string('status'); // pending, paid, cancelled

        $table->timestamp('issued_at');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
