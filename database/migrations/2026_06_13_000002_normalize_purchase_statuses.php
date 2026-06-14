<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('purchases')->where('status', 'pending')->update(['status' => 'draft']);
        DB::table('purchases')->where('status', 'completed')->update(['status' => 'received']);
    }

    public function down(): void
    {
        DB::table('purchases')->where('status', 'draft')->update(['status' => 'pending']);
        DB::table('purchases')->where('status', 'received')->update(['status' => 'completed']);
    }
};
