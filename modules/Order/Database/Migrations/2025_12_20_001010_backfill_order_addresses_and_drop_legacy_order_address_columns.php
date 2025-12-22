<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('orders')
            ->select([
                'id',
                'billing_first_name',
                'billing_last_name',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_state',
                'billing_zip',
                'billing_country',
                'billing_phone',
                'invoice_title',
                'invoice_tax_number',
                'invoice_tax_office',
                'shipping_first_name',
                'shipping_last_name',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_state',
                'shipping_zip',
                'shipping_country',
                'shipping_phone',
            ])
            ->orderBy('id')
            ->chunkById(200, function ($orders) use ($now) {
                foreach ($orders as $o) {
                    $hasShipping = DB::table('order_addresses')
                        ->where('order_id', $o->id)
                        ->where('type', 'shipping')
                        ->exists();

                    if (! $hasShipping) {
                        DB::table('order_addresses')->insert([
                            'order_id' => $o->id,
                            'type' => 'shipping',
                            'first_name' => $o->shipping_first_name,
                            'last_name' => $o->shipping_last_name,
                            'company_name' => null,
                            'tax_number' => null,
                            'tax_office' => null,
                            'phone' => $o->shipping_phone,
                            'city' => $o->shipping_city,
                            'district' => $o->shipping_state,
                            'zip' => $o->shipping_zip,
                            'country' => $o->shipping_country,
                            'address_line' => $o->shipping_address_1,
                            'address_2' => $o->shipping_address_2,
                            'billing_email' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $hasBilling = DB::table('order_addresses')
                        ->where('order_id', $o->id)
                        ->where('type', 'billing')
                        ->exists();

                    if (! $hasBilling) {
                        DB::table('order_addresses')->insert([
                            'order_id' => $o->id,
                            'type' => 'billing',
                            'first_name' => $o->billing_first_name,
                            'last_name' => $o->billing_last_name,
                            'company_name' => $o->invoice_title,
                            'tax_number' => $o->invoice_tax_number,
                            'tax_office' => $o->invoice_tax_office,
                            'phone' => $o->billing_phone,
                            'city' => $o->billing_city,
                            'district' => $o->billing_state,
                            'zip' => $o->billing_zip,
                            'country' => $o->billing_country,
                            'address_line' => $o->billing_address_1,
                            'address_2' => $o->billing_address_2,
                            'billing_email' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            });

        Schema::table('orders', function (Blueprint $table) {
            $cols = [
                'billing_first_name',
                'billing_last_name',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_state',
                'billing_zip',
                'billing_country',
                'billing_phone',
                'invoice_title',
                'invoice_tax_number',
                'invoice_tax_office',
                'shipping_first_name',
                'shipping_last_name',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_state',
                'shipping_zip',
                'shipping_country',
                'shipping_phone',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        // no-op
    }
};
