<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_charts', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('type');
            $table->longText('content_html')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('size_chart_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('size_chart_id');
            $table->string('locale')->index();
            $table->string('title');
            $table->unique(['size_chart_id', 'locale']);

            $table->foreign('size_chart_id')
                ->references('id')
                ->on('size_charts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_chart_translations');
        Schema::dropIfExists('size_charts');
    }
};
