<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_upsell_offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rule_id');
            $table->integer('order')->default(0); // Sıralama
            $table->string('trigger')->default('rejected'); // 'accepted', 'rejected', 'always'
            
            // Ürün bilgileri
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variant_id')->nullable();
            
            // İndirim bilgileri
            $table->string('discount_type')->default('none'); // none, percent, fixed
            $table->decimal('discount_value', 18, 4)->default(0);
            
            // Metinler
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('rule_id')
                ->references('id')->on('cart_upsell_rules')
                ->onDelete('cascade');
                
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');
                
            $table->foreign('variant_id')
                ->references('id')->on('product_variants')
                ->onDelete('set null');
                
            $table->index(['rule_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_upsell_offers');
    }
};
