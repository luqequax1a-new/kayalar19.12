<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_tracking_number')->nullable();
            $table->string('shipping_carrier_name')->nullable();
            $table->string('shipping_tracking_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_tracking_number', 'shipping_carrier_name', 'shipping_tracking_url']);
        });
    }
};

