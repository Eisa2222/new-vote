<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $t) {
            $t->id();
            $t->string('name_ar', 120);
            $t->string('name_en', 120);
            $t->string('short_name', 20)->nullable();
            $t->string('logo_path')->nullable();
            $t->enum('status', ['active', 'inactive'])->default('active')->index();
            $t->timestamps();
            $t->softDeletes();

            $t->unique('name_ar');
            $t->unique('name_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
