<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('addresses')) {
            return;
        }

        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'type')) {
                $table->enum('type', ['shipping', 'billing'])->nullable();
            }

            if (!Schema::hasColumn('addresses', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }

            try {
                $table->string('first_name')->nullable()->change();
                $table->string('last_name')->nullable()->change();
            } catch (\Throwable $e) {
                try {
                    DB::statement('ALTER TABLE addresses MODIFY first_name VARCHAR(191) NULL');
                    DB::statement('ALTER TABLE addresses MODIFY last_name VARCHAR(191) NULL');
                } catch (\Throwable $e2) {
                }
            }

            if (!Schema::hasColumn('addresses', 'company_name')) {
                $table->string('company_name')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'tax_number')) {
                $table->string('tax_number', 50)->nullable();
            }
            if (!Schema::hasColumn('addresses', 'tax_office')) {
                $table->string('tax_office')->nullable();
            }

            if (!Schema::hasColumn('addresses', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'district_id')) {
                $table->unsignedBigInteger('district_id')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'address_line')) {
                $table->text('address_line')->nullable();
            }
        });

        try {
            DB::statement('UPDATE addresses SET user_id = customer_id WHERE user_id IS NULL');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('addresses')) {
            return;
        }

        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('addresses', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('addresses', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('addresses', 'tax_number')) {
                $table->dropColumn('tax_number');
            }
            if (Schema::hasColumn('addresses', 'tax_office')) {
                $table->dropColumn('tax_office');
            }
            if (Schema::hasColumn('addresses', 'city_id')) {
                $table->dropColumn('city_id');
            }
            if (Schema::hasColumn('addresses', 'district_id')) {
                $table->dropColumn('district_id');
            }
            if (Schema::hasColumn('addresses', 'address_line')) {
                $table->dropColumn('address_line');
            }
        });
    }
};

