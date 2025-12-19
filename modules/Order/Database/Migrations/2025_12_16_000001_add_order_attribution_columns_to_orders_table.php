<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'traffic_source')) {
                $table->string('traffic_source')->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'utm_source')) {
                $table->string('utm_source')->nullable();
            }
            if (! Schema::hasColumn('orders', 'utm_medium')) {
                $table->string('utm_medium')->nullable();
            }
            if (! Schema::hasColumn('orders', 'utm_campaign')) {
                $table->string('utm_campaign')->nullable();
            }
            if (! Schema::hasColumn('orders', 'utm_term')) {
                $table->string('utm_term')->nullable();
            }
            if (! Schema::hasColumn('orders', 'utm_content')) {
                $table->string('utm_content')->nullable();
            }
            if (! Schema::hasColumn('orders', 'referrer_url')) {
                $table->text('referrer_url')->nullable();
            }
            if (! Schema::hasColumn('orders', 'landing_url')) {
                $table->text('landing_url')->nullable();
            }
            if (! Schema::hasColumn('orders', 'click_id_gclid')) {
                $table->string('click_id_gclid')->nullable();
            }
            if (! Schema::hasColumn('orders', 'click_id_fbclid')) {
                $table->string('click_id_fbclid')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'traffic_source')) {
                $table->dropIndex(['traffic_source']);
                $table->dropColumn('traffic_source');
            }
            if (Schema::hasColumn('orders', 'utm_source')) {
                $table->dropColumn('utm_source');
            }
            if (Schema::hasColumn('orders', 'utm_medium')) {
                $table->dropColumn('utm_medium');
            }
            if (Schema::hasColumn('orders', 'utm_campaign')) {
                $table->dropColumn('utm_campaign');
            }
            if (Schema::hasColumn('orders', 'utm_term')) {
                $table->dropColumn('utm_term');
            }
            if (Schema::hasColumn('orders', 'utm_content')) {
                $table->dropColumn('utm_content');
            }
            if (Schema::hasColumn('orders', 'referrer_url')) {
                $table->dropColumn('referrer_url');
            }
            if (Schema::hasColumn('orders', 'landing_url')) {
                $table->dropColumn('landing_url');
            }
            if (Schema::hasColumn('orders', 'click_id_gclid')) {
                $table->dropColumn('click_id_gclid');
            }
            if (Schema::hasColumn('orders', 'click_id_fbclid')) {
                $table->dropColumn('click_id_fbclid');
            }
        });
    }
};
