<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medicine_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicine_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicine_form_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_form_id')->constrained('medicine_forms')->cascadeOnDelete();
            $table->foreignId('medicine_unit_id')->constrained('medicine_units')->cascadeOnDelete();
            $table->timestamps();

            // Prevent duplicate mappings
            $table->unique(['medicine_form_id', 'medicine_unit_id']);
        });

        // Add columns to medicines
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['form', 'packing_unit']);
            $table->foreignId('medicine_form_id')->nullable()->after('manufacturer_id')->constrained('medicine_forms')->nullOnDelete();
            $table->foreignId('medicine_unit_id')->nullable()->after('medicine_form_id')->constrained('medicine_units')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropForeign(['medicine_form_id']);
            $table->dropForeign(['medicine_unit_id']);
            $table->dropColumn(['medicine_form_id', 'medicine_unit_id']);
            
            $table->string('form')->nullable()->after('manufacturer_id');
            $table->string('packing_unit')->nullable()->after('packing_quantity');
        });

        Schema::dropIfExists('medicine_form_unit');
        Schema::dropIfExists('medicine_units');
        Schema::dropIfExists('medicine_forms');
    }
};
