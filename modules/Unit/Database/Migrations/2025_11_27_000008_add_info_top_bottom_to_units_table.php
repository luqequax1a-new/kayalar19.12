<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'info_top')) {
                $table->text('info_top')->nullable();
            }
            if (!Schema::hasColumn('units', 'info_bottom')) {
                $table->text('info_bottom')->nullable();
            }
        });

        if (Schema::hasColumn('units', 'info') && Schema::hasColumn('units', 'info_top')) {
            \DB::statement('UPDATE units SET info_top = COALESCE(info_top, info)');
        }
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'info_bottom')) {
                $table->dropColumn('info_bottom');
            }
            if (Schema::hasColumn('units', 'info_top')) {
                $table->dropColumn('info_top');
            }
        });
    }
};

