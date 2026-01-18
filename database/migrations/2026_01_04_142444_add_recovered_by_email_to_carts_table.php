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
        if (!Schema::hasColumn('carts', 'recovered_by_email')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->string('recovered_by_email')->nullable()->after('order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('carts', 'recovered_by_email')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn('recovered_by_email');
            });
        }
    }
};
