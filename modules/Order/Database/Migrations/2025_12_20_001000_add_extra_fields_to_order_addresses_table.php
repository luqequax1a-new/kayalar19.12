<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('order_addresses', 'address_2')) {
                $table->string('address_2')->nullable();
            }
            if (! Schema::hasColumn('order_addresses', 'zip')) {
                $table->string('zip')->nullable();
            }
            if (! Schema::hasColumn('order_addresses', 'country')) {
                $table->string('country')->nullable();
            }
            if (! Schema::hasColumn('order_addresses', 'billing_email')) {
                $table->string('billing_email')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_addresses', function (Blueprint $table) {
            foreach (['address_2', 'zip', 'country', 'billing_email'] as $col) {
                if (Schema::hasColumn('order_addresses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
