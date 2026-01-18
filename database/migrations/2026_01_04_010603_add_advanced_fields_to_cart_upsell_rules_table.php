<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cart_upsell_rules', function (Blueprint $table) {
            $table->unsignedInteger('main_category_id')->nullable()->after('main_product_id');
            $table->boolean('exclude_discounted_products')->default(false)->after('hide_if_already_in_cart');
            $table->integer('usage_limit')->nullable()->after('sort_order');
            $table->json('description')->nullable()->after('subtitle');

            $table->foreign('main_category_id')
                ->references('id')->on('categories')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cart_upsell_rules', function (Blueprint $table) {
            $table->dropForeign(['main_category_id']);
            $table->dropColumn(['main_category_id', 'exclude_discounted_products', 'usage_limit', 'description']);
        });
    }
};
