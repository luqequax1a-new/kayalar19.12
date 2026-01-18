<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'geliver_shipment_id')) {
                $table->string('geliver_shipment_id')->nullable();
            }
            if (!Schema::hasColumn('orders', 'geliver_shipment_payload')) {
                $table->text('geliver_shipment_payload')->nullable();
            }
            if (!Schema::hasColumn('orders', 'geliver_last_status')) {
                $table->string('geliver_last_status')->nullable();
            }
            if (!Schema::hasColumn('orders', 'geliver_last_status_at')) {
                $table->dateTime('geliver_last_status_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'geliver_shipment_id',
                'geliver_shipment_payload',
                'geliver_last_status',
                'geliver_last_status_at',
            ] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

