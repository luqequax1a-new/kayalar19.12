<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('entity_files')) {
            Schema::table('entity_files', function (Blueprint $table) {
                if (!Schema::hasColumn('entity_files','alt_text')) {
                    $table->string('alt_text')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('entity_files')) {
            Schema::table('entity_files', function (Blueprint $table) {
                if (Schema::hasColumn('entity_files','alt_text')) {
                    $table->dropColumn('alt_text');
                }
            });
        }
    }
};
