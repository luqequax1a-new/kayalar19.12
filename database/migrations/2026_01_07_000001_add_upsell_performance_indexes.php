<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_upsell_rules', function (Blueprint $table) {
            // Composite index for common query pattern in resolveBestRule
            $table->index(['status', 'show_on', 'trigger_type'], 'idx_rules_active_placement_trigger');
            
            // Index for date range queries
            $table->index(['starts_at', 'ends_at'], 'idx_rules_date_range');
            
            // Index for sort order
            $table->index('sort_order', 'idx_rules_sort_order');
        });

        Schema::table('cart_upsell_offers', function (Blueprint $table) {
            // Composite index for offers by rule and order
            $table->index(['rule_id', 'order'], 'idx_offers_rule_order');
            
            // Index for product lookups
            $table->index('product_id', 'idx_offers_product');
        });
    }

    public function down(): void
    {
        Schema::table('cart_upsell_rules', function (Blueprint $table) {
            $table->dropIndex('idx_rules_active_placement_trigger');
            $table->dropIndex('idx_rules_date_range');
            $table->dropIndex('idx_rules_sort_order');
        });

        Schema::table('cart_upsell_offers', function (Blueprint $table) {
            $table->dropIndex('idx_offers_rule_order');
            $table->dropIndex('idx_offers_product');
        });
    }
};
