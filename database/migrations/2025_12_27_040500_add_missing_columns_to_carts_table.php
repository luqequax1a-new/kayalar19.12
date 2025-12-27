<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index();
            }

            if (!Schema::hasColumn('carts', 'customer_email')) {
                $table->string('customer_email')->nullable()->index();
            }

            if (!Schema::hasColumn('carts', 'customer_first_name')) {
                $table->string('customer_first_name')->nullable();
            }

            if (!Schema::hasColumn('carts', 'customer_last_name')) {
                $table->string('customer_last_name')->nullable();
            }

            if (!Schema::hasColumn('carts', 'customer_phone')) {
                $table->string('customer_phone')->nullable();
            }

            if (!Schema::hasColumn('carts', 'is_recovered')) {
                $table->boolean('is_recovered')->default(false)->index();
            }

            if (!Schema::hasColumn('carts', 'recovered_at')) {
                $table->timestamp('recovered_at')->nullable()->index();
            }

            if (!Schema::hasColumn('carts', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->index();
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
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'order_id')) {
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('carts', 'recovered_at')) {
                $table->dropColumn('recovered_at');
            }
            if (Schema::hasColumn('carts', 'is_recovered')) {
                $table->dropColumn('is_recovered');
            }
            if (Schema::hasColumn('carts', 'customer_phone')) {
                $table->dropColumn('customer_phone');
            }
            if (Schema::hasColumn('carts', 'customer_last_name')) {
                $table->dropColumn('customer_last_name');
            }
            if (Schema::hasColumn('carts', 'customer_first_name')) {
                $table->dropColumn('customer_first_name');
            }
            if (Schema::hasColumn('carts', 'customer_email')) {
                $table->dropColumn('customer_email');
            }
            if (Schema::hasColumn('carts', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
