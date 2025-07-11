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
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Manufacturer name
            $table->string('contact_name')->nullable(); // Name of the contact person (optional)
            $table->string('phone')->nullable(); // Manufacturer's contact phone
            $table->string('email')->nullable()->unique(); // Contact email (optional)
            $table->string('address')->nullable(); // Address (optional)
            $table->string('website')->nullable(); // Website (optional)
            $table->string('country')->nullable(); // Manufacturer's country (optional)
            $table->boolean('is_active')->default(true); // Soft delete flag (active or inactive)
            $table->timestamps(); // Laravel's created_at & updated_at
            $table->softDeletes(); // For soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturers');
    }
};
