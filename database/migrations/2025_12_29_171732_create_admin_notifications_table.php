<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // new_order, abandoned_cart, new_customer, low_stock, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Extra data (order_id, customer_id, etc.)
            $table->string('icon')->nullable(); // Icon class
            $table->string('color')->default('blue'); // blue, green, orange, red
            $table->string('link')->nullable(); // URL to navigate
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['is_read', 'created_at']);
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_notifications');
    }
};
