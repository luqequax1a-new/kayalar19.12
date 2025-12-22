<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('guest_email')->nullable()->index();
            $table->string('source')->nullable()->index();
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->integer('sender_id')->unsigned()->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['guest_email', 'source']);
            $table->integer('user_id')->unsigned()->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->integer('sender_id')->unsigned()->nullable(false)->change();
        });
    }
};
