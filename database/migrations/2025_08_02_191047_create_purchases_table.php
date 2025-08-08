<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            // The branch where stock is received
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');

            // Supplier (optional — can be null if walk-in purchase)
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');

            //Reference Code
            $table->string('ref_code_prefix')->nullable();
            $table->string('ref_code_count');
            
            $table->string('invoice_number')->nullable();
            $table->date('purchase_date')->default(now());

            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Quick lookup index
            $table->index(['branch_id', 'purchase_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
