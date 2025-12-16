<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('url_redirects')) {
            return;
        }

        Schema::table('url_redirects', function (Blueprint $table) {
            try {
                $table->index(['is_active', 'source_path'], 'url_redirects_is_active_source_path_index');
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('url_redirects')) {
            return;
        }

        Schema::table('url_redirects', function (Blueprint $table) {
            try {
                $table->dropIndex('url_redirects_is_active_source_path_index');
            } catch (\Throwable $e) {
            }
        });
    }
};
