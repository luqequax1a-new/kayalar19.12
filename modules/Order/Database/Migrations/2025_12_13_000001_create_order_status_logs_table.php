<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_status_logs')) {
            return;
        }
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('order_id');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('source')->nullable();
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('to_status');
            $table->index('source');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};
