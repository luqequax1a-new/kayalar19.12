<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Order\Entities\Order;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->nullable()->unique();
            }
        });

        try {
            if (Schema::hasColumn('orders', 'order_number')) {
                Order::query()
                    ->whereNull('order_number')
                    ->orWhere('order_number', '')
                    ->orderBy('id')
                    ->chunkById(200, function ($orders) {
                        foreach ($orders as $order) {
                            $order->save();
                        }
                    });
            }
        } catch (\Throwable $e) {
        }
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'order_number')) {
                $table->dropUnique(['order_number']);
                $table->dropColumn('order_number');
            }
        });
    }
};
