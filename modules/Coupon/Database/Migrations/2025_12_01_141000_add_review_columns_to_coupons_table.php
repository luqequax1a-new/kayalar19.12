<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'is_review_coupon')) {
                $table->boolean('is_review_coupon')->default(false);
            }
            if (!Schema::hasColumn('coupons', 'review_id')) {
                $table->unsignedInteger('review_id')->nullable();
            }
            if (!Schema::hasColumn('coupons', 'order_id')) {
                $table->unsignedInteger('order_id')->nullable();
            }
            if (!Schema::hasColumn('coupons', 'customer_id')) {
                $table->unsignedInteger('customer_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            foreach (['is_review_coupon', 'review_id', 'order_id', 'customer_id'] as $col) {
                if (Schema::hasColumn('coupons', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

