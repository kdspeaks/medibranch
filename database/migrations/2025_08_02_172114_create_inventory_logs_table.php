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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_batch_id')->constrained()->onDelete('cascade');

            // Transaction type
            $table->enum('type', ['in', 'out', 'adjustment']);

            $table->integer('quantity');
            $table->string('reason')->nullable();

            // Morphs for linking to purchases, sales, adjustments
            $table->nullableMorphs('source');

            $table->timestamps();

            // Index for faster source queries
            $table->index(['inventory_batch_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
