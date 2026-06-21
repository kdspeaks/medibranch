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
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('stored_location');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->string('stored_location')->nullable()->after('medicine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('stored_location');
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->string('stored_location')->nullable()->after('packing_quantity');
        });
    }
};
