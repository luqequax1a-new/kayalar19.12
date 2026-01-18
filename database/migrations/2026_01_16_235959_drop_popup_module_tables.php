<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('popup_variant_translations');
        Schema::dropIfExists('popup_variants');
        Schema::dropIfExists('popup_interactions');
        Schema::dropIfExists('popup_translations');
        Schema::dropIfExists('popups');
    }

    public function down(): void
    {
        // Intentionally left empty.
    }
};
