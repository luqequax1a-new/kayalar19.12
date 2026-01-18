<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (! Schema::hasColumn('coupons', 'redeemed_order_id')) {
                $table->unsignedInteger('redeemed_order_id')->nullable();
            }

            if (! Schema::hasColumn('coupons', 'redeemed_at')) {
                $table->timestamp('redeemed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (Schema::hasColumn('coupons', 'redeemed_order_id')) {
                $table->dropColumn('redeemed_order_id');
            }
            if (Schema::hasColumn('coupons', 'redeemed_at')) {
                $table->dropColumn('redeemed_at');
            }
        });
    }
};

