<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('addresses')) {
            return;
        }

        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'address_title')) {
                $table->string('address_title')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('addresses')) {
            return;
        }

        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'address_title')) {
                $table->dropColumn('address_title');
            }
        });
    }
};
