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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');             // Total added
            $table->integer('available_quantity');   // Remaining in this batch
            $table->decimal('unit_purchase_price', 10, 2);
            $table->decimal('margin', 10, 2);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            // Status for batch control
            $table->enum('status', ['active', 'expired', 'quarantined'])->default('active');

            $table->timestamps();

            // Index for batch queries by expiry or inventory
            $table->index(['inventory_id', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
