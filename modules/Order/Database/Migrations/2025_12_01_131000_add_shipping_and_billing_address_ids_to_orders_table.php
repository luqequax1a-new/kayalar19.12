<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->dropColumn('shipping_address_id');
            }
            if (Schema::hasColumn('orders', 'billing_address_id')) {
                $table->dropColumn('billing_address_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('shipping_address_id')->nullable();
            $table->unsignedInteger('billing_address_id')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->foreign('shipping_address_id')->references('id')->on('addresses')->onDelete('set null');
            } catch (\Throwable $e) {
            }
            try {
                $table->foreign('billing_address_id')->references('id')->on('addresses')->onDelete('set null');
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            try { $table->dropForeign(['shipping_address_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['billing_address_id']); } catch (\Throwable $e) {}
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->dropColumn('shipping_address_id');
            }
            if (Schema::hasColumn('orders', 'billing_address_id')) {
                $table->dropColumn('billing_address_id');
            }
        });
    }
};
