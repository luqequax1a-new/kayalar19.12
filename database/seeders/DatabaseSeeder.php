<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // 1. Core Settings & Data
        $this->call([
            \Modules\Setting\Database\Seeders\SettingDatabaseSeeder::class,
            \Modules\Translation\Database\Seeders\TranslationDatabaseSeeder::class,
        ]);

        // 2. User & Roles
        $this->call([
            DemoSuperAdminSeeder::class,
        ]);

        // 3. Other Module Seeding (If classes exist)
        $optionalSeeders = [
            'Modules\Category\Database\Seeders\CategoryDatabaseSeeder',
            'Modules\Attribute\Database\Seeders\AttributeDatabaseSeeder',
            'Modules\Menu\Database\Seeders\MenuDatabaseSeeder',
            'Modules\Page\Database\Seeders\PageDatabaseSeeder',
            'Modules\Slider\Database\Seeders\SliderDatabaseSeeder',
        ];

        foreach ($optionalSeeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
        
        // 4. Finalize
        try {
            Artisan::call('translations:import', ['--force' => true]);
        } catch (\Exception $e) {
            // Import command might not be available
        }
    }
}
