<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // e.g. Arsenic Album
            $table->string('barcode')->unique(); // used as unique ID for scanning
            $table->string('sku')->unique(); // used as unique ID for scanning

            $table->foreignId('manufacturer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('potency')->nullable();    // e.g. 30CH, 200CH
            $table->string('form')->nullable();       // e.g. Dilution, Tablet, Syrup
            $table->integer('packing_quantity');
            $table->string('packing_unit');

            $table->decimal('purchase_price', 10, 2)->default(0.00);
            $table->decimal('margin', 10, 2)->default(0.00);

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes(); // For soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
