<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('products', 'primary_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('primary_category_id');
            });
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('primary_category_id')->nullable();

            $table->foreign('primary_category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['primary_category_id']);
            $table->dropColumn('primary_category_id');
        });
    }
};
