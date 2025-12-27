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
        // Products table indexes for listing queries
        Schema::table('products', function (Blueprint $table) {
            // Index for price filtering and sorting
            $table->index(['price', 'special_price'], 'products_price_special_price_index');
            
            // Index for stock filtering
            $table->index(['in_stock', 'manage_stock'], 'products_stock_index');
            
            // Index for new products filtering
            $table->index(['new_from', 'new_to'], 'products_new_dates_index');
            
            // Index for active products
            $table->index('is_active', 'products_is_active_index');
            
            // Composite index for common listing queries
            $table->index(['is_active', 'in_stock', 'price'], 'products_listing_composite_index');
        });

        // Product categories table index for category filtering
        Schema::table('product_categories', function (Blueprint $table) {
            // Composite index for category product lookups
            $table->index(['category_id', 'product_id'], 'product_categories_category_product_index');
        });

        // Product translations table index for search
        Schema::table('product_translations', function (Blueprint $table) {
            // Index for name searches
            $table->index(['locale', 'name'], 'product_translations_locale_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_price_special_price_index');
            $table->dropIndex('products_stock_index');
            $table->dropIndex('products_new_dates_index');
            $table->dropIndex('products_is_active_index');
            $table->dropIndex('products_listing_composite_index');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex('product_categories_category_product_index');
        });

        Schema::table('product_translations', function (Blueprint $table) {
            $table->dropIndex('product_translations_locale_name_index');
        });
    }
};
