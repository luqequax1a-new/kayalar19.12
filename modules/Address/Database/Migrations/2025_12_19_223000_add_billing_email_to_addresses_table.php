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
            if (! Schema::hasColumn('addresses', 'billing_email')) {
                $table->string('billing_email')->nullable()->after('invoice_tax_office');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('addresses')) {
            return;
        }

        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'billing_email')) {
                $table->dropColumn('billing_email');
            }
        });
    }
};
