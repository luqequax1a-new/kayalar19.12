<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'review_request_sent_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('review_request_sent_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'review_request_sent_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('review_request_sent_at');
            });
        }
    }
};

