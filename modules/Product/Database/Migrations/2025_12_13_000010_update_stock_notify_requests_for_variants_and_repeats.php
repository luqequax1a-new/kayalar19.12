<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('stock_notify_requests', 'variant_id')) {
            Schema::table('stock_notify_requests', function (Blueprint $table) {
                $table->unsignedInteger('variant_id')->nullable();
            });
        }

        $hasIndex = function (string $name): bool {
            return collect(DB::select('SHOW INDEX FROM stock_notify_requests'))
                ->firstWhere('Key_name', $name) !== null;
        };

        $fkNameFor = function (string $column): ?string {
            $row = collect(DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'stock_notify_requests'
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME IS NOT NULL
                 LIMIT 1",
                [$column]
            ))->first();

            return $row ? $row->CONSTRAINT_NAME : null;
        };

        $productFk = $fkNameFor('product_id');

        if ($productFk) {
            Schema::table('stock_notify_requests', function (Blueprint $table) use ($productFk) {
                $table->dropForeign($productFk);
            });
        }

        if (! $hasIndex('stock_notify_requests_product_id_index')) {
            Schema::table('stock_notify_requests', function (Blueprint $table) {
                $table->index('product_id', 'stock_notify_requests_product_id_index');
            });
        }

        if ($hasIndex('stock_notify_requests_product_id_email_unique')) {
            Schema::table('stock_notify_requests', function (Blueprint $table) {
                $table->dropUnique('stock_notify_requests_product_id_email_unique');
            });
        }

        if (! $hasIndex('stock_notify_requests_product_id_variant_id_sent_at_index')) {
            Schema::table('stock_notify_requests', function (Blueprint $table) {
                $table->index(
                    ['product_id', 'variant_id', 'sent_at'],
                    'stock_notify_requests_product_id_variant_id_sent_at_index'
                );
            });
        }

        if (! $fkNameFor('product_id')) {
            Schema::table('stock_notify_requests', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('cascade');
            });
        }

        if (! $fkNameFor('variant_id')) {
            Schema::table('stock_notify_requests', function (Blueprint $table) {
                $table->foreign('variant_id')
                    ->references('id')
                    ->on('product_variants')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        if (! Schema::hasTable('stock_notify_requests')) {
            return;
        }

        $fkNameFor = function (string $column): ?string {
            $row = collect(DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'stock_notify_requests'
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME IS NOT NULL
                 LIMIT 1",
                [$column]
            ))->first();

            return $row ? $row->CONSTRAINT_NAME : null;
        };

        if (Schema::hasColumn('stock_notify_requests', 'variant_id')) {
            $variantFk = $fkNameFor('variant_id');

            if ($variantFk) {
                Schema::table('stock_notify_requests', function (Blueprint $table) use ($variantFk) {
                    $table->dropForeign($variantFk);
                });
            }

            if (collect(DB::select('SHOW INDEX FROM stock_notify_requests'))->firstWhere('Key_name', 'stock_notify_requests_product_id_variant_id_sent_at_index')) {
                Schema::table('stock_notify_requests', function (Blueprint $table) {
                    $table->dropIndex('stock_notify_requests_product_id_variant_id_sent_at_index');
                });
            }

            if (! collect(DB::select('SHOW INDEX FROM stock_notify_requests'))->firstWhere('Key_name', 'stock_notify_requests_product_id_email_unique')) {
                Schema::table('stock_notify_requests', function (Blueprint $table) {
                    $table->unique(['product_id', 'email'], 'stock_notify_requests_product_id_email_unique');
                });
            }

            Schema::table('stock_notify_requests', function (Blueprint $table) {
                $table->dropColumn('variant_id');
            });
        }
    }
};
