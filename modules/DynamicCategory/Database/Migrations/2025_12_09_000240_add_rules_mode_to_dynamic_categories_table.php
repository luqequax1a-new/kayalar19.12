<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dynamic_categories', function (Blueprint $table) {
            $table->string('rules_mode', 10)->default('all');
        });
    }

    public function down(): void
    {
        Schema::table('dynamic_categories', function (Blueprint $table) {
            $table->dropColumn('rules_mode');
        });
    }
};
