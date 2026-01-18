<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sync existing product slugs
        DB::table('products')->whereNotNull('slug')->orderBy('id')->chunk(100, function ($products) {
            foreach ($products as $product) {
                DB::table('url_slugs')->updateOrInsert(
                    ['type' => 'product', 'entity_id' => $product->id],
                    [
                        'slug' => $product->slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });

        // Sync existing category slugs
        DB::table('categories')->whereNotNull('slug')->orderBy('id')->chunk(100, function ($categories) {
            foreach ($categories as $category) {
                DB::table('url_slugs')->updateOrInsert(
                    ['type' => 'category', 'entity_id' => $category->id],
                    [
                        'slug' => $category->slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });

        // Sync existing page slugs
        DB::table('pages')->whereNotNull('slug')->orderBy('id')->chunk(100, function ($pages) {
            foreach ($pages as $page) {
                DB::table('url_slugs')->updateOrInsert(
                    ['type' => 'page', 'entity_id' => $page->id],
                    [
                        'slug' => $page->slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('url_slugs')->truncate();
    }
};
