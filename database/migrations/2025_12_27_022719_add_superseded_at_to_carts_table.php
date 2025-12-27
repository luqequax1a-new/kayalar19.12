<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupersededAtToCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('carts', 'superseded_at')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->timestamp('superseded_at')->nullable();
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
        if (Schema::hasColumn('carts', 'superseded_at')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn('superseded_at');
            });
        }
    }
}
