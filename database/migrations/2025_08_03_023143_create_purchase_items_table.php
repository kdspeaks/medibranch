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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->nullable()->constrained()->onDelete('set null');

            $table->integer('quantity');
            $table->decimal('unit_purchase_price', 10, 2);
            $table->decimal('unit_selling_price', 10, 2);
            $table->string('batch_number')->nullable();
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->foreignId('tax_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, stocked, cancelled

            $table->timestamps();

            $table->index(['purchase_id', 'medicine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
