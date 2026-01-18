<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_unit_id')->nullable();
            $table->foreign('sale_unit_id')->references('id')->on('units')->onDelete('set null');
            $table->index('sale_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['sale_unit_id']);
            $table->dropIndex(['sale_unit_id']);
            $table->dropColumn('sale_unit_id');
        });
    }
};

