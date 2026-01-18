<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dynamic_category_rules', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0);
            $table->string('label')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dynamic_category_rules', function (Blueprint $table) {
            $table->dropColumn(['position', 'label']);
        });
    }
};
