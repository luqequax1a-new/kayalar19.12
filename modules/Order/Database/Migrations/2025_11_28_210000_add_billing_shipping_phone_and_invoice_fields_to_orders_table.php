<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'billing_phone')) {
                $table->string('billing_phone')->nullable();
            }

            if (!Schema::hasColumn('orders', 'shipping_phone')) {
                $table->string('shipping_phone')->nullable();
            }

            if (!Schema::hasColumn('orders', 'invoice_title')) {
                $table->string('invoice_title')->nullable();
            }

            if (!Schema::hasColumn('orders', 'invoice_tax_number')) {
                $table->string('invoice_tax_number')->nullable();
            }

            if (!Schema::hasColumn('orders', 'invoice_tax_office')) {
                $table->string('invoice_tax_office')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['billing_phone', 'shipping_phone', 'invoice_title', 'invoice_tax_number', 'invoice_tax_office'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
