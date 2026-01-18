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
        Schema::table('url_slugs', function (Blueprint $table) {
            // Drop the enum column and recreate as string or modify it. 
            // Since modifying enum to string might be tricky in some DBs without raw SQL or doctrine,
            // but Laravel's string() usually handles VARCHAR.
            // However, modifying a column requires doctrine/dbal.
            // Let's assume user has it or use DB::statement for safety.
            $table->string('type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('url_slugs', function (Blueprint $table) {
            // Revert is harder because we don't know the exact previous state perfectly without hardcoding, 
            // but let's try.
            // $table->enum('type', ['product', 'category', 'page'])->change();
        });
    }
};
