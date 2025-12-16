<?php

use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::whereTranslation('name', 'Admin')->first();
        if (!$role) {
            return;
        }

        $existing = is_array($role->permissions) ? $role->permissions : (json_decode($role->permissions, true) ?: []);

        $existing['admin.geliver_settings.edit'] = true;
        $existing['admin.geliver_orders.send'] = true;

        $role->permissions = $existing;
        $role->save();
    }

    public function down(): void
    {
        $role = Role::whereTranslation('name', 'Admin')->first();
        if (!$role) {
            return;
        }

        $existing = is_array($role->permissions) ? $role->permissions : (json_decode($role->permissions, true) ?: []);
        unset($existing['admin.geliver_settings.edit'], $existing['admin.geliver_orders.send']);

        $role->permissions = $existing;
        $role->save();
    }
};
