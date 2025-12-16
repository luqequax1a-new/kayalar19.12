<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_chart_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('size_chart_id');
            $table->morphs('assignable');
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->unique(['size_chart_id', 'assignable_type', 'assignable_id'], 'sca_unique');

            $table->foreign('size_chart_id')
                ->references('id')
                ->on('size_charts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_chart_assignments');
    }
};
