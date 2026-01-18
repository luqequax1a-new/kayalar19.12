<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPerformanceIndexesToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Add index for search queries (is_active, in_stock, viewed)
            $table->index(['is_active', 'in_stock', 'viewed'], 'products_active_stock_viewed_idx');
            
            // Add index for SKU searches
            $table->index('sku', 'products_sku_idx');
            
            // Add index for viewed sorting
            $table->index('viewed', 'products_viewed_idx');
        });
        
        Schema::table('product_translations', function (Blueprint $table) {
            // Add index for name searches
            if (!Schema::hasColumn('product_translations', 'name_idx')) {
                $table->index('name', 'product_translations_name_idx');
            }
        });
        
        Schema::table('search_terms', function (Blueprint $table) {
            // Add index for popular searches
            $table->index('hits', 'search_terms_hits_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_stock_viewed_idx');
            $table->dropIndex('products_sku_idx');
            $table->dropIndex('products_viewed_idx');
        });
        
        Schema::table('product_translations', function (Blueprint $table) {
            if (Schema::hasColumn('product_translations', 'name_idx')) {
                $table->dropIndex('product_translations_name_idx');
            }
        });
        
        Schema::table('search_terms', function (Blueprint $table) {
            $table->dropIndex('search_terms_hits_idx');
        });
    }
}
