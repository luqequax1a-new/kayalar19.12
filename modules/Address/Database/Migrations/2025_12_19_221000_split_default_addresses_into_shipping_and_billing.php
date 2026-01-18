<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('default_addresses')) {
            return;
        }

        Schema::table('default_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('default_addresses', 'default_shipping_address_id')) {
                $table->unsignedInteger('default_shipping_address_id')->nullable();
            }

            if (! Schema::hasColumn('default_addresses', 'default_billing_address_id')) {
                $table->unsignedInteger('default_billing_address_id')->nullable();
            }
        });

        if (Schema::hasColumn('default_addresses', 'address_id')) {
            try {
                DB::statement('UPDATE default_addresses SET default_shipping_address_id = address_id WHERE default_shipping_address_id IS NULL');
                DB::statement('UPDATE default_addresses SET default_billing_address_id = address_id WHERE default_billing_address_id IS NULL');
            } catch (\Throwable $e) {
            }
        }

        Schema::table('default_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('default_addresses', 'address_id')) {
                try {
                    $table->dropForeign(['address_id']);
                } catch (\Throwable $e) {
                }

                try {
                    $table->dropColumn('address_id');
                } catch (\Throwable $e) {
                }
            }

            if (Schema::hasColumn('default_addresses', 'default_shipping_address_id')) {
                try {
                    $table->foreign('default_shipping_address_id')->references('id')->on('addresses')->nullOnDelete();
                } catch (\Throwable $e) {
                }
            }

            if (Schema::hasColumn('default_addresses', 'default_billing_address_id')) {
                try {
                    $table->foreign('default_billing_address_id')->references('id')->on('addresses')->nullOnDelete();
                } catch (\Throwable $e) {
                }
            }

            try {
                $table->unique('customer_id');
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('default_addresses')) {
            return;
        }

        Schema::table('default_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('default_addresses', 'address_id')) {
                $table->unsignedInteger('address_id')->nullable();
            }
        });

        try {
            DB::statement('UPDATE default_addresses SET address_id = COALESCE(default_shipping_address_id, default_billing_address_id) WHERE address_id IS NULL');
        } catch (\Throwable $e) {
        }

        Schema::table('default_addresses', function (Blueprint $table) {
            try {
                $table->dropUnique(['customer_id']);
            } catch (\Throwable $e) {
            }

            try {
                $table->dropForeign(['default_shipping_address_id']);
            } catch (\Throwable $e) {
            }

            try {
                $table->dropForeign(['default_billing_address_id']);
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('default_addresses', 'default_shipping_address_id')) {
                try {
                    $table->dropColumn('default_shipping_address_id');
                } catch (\Throwable $e) {
                }
            }

            if (Schema::hasColumn('default_addresses', 'default_billing_address_id')) {
                try {
                    $table->dropColumn('default_billing_address_id');
                } catch (\Throwable $e) {
                }
            }

            try {
                $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            } catch (\Throwable $e) {
            }
        });
    }
};
