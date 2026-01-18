<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cart_upsell_rules', function (Blueprint $table) {
            $table->unsignedInteger('times_shown')->default(0);
            $table->unsignedInteger('times_added_to_cart')->default(0);
            $table->unsignedInteger('times_purchased')->default(0);
            $table->decimal('total_revenue', 18, 4)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('cart_upsell_rules', function (Blueprint $table) {
            $table->dropColumn(['times_shown', 'times_added_to_cart', 'times_purchased', 'total_revenue']);
        });
    }
};
