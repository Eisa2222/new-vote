<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sports', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 60)->unique();
            $t->string('name_ar', 100);
            $t->string('name_en', 100);
            $t->enum('status', ['active', 'inactive'])->default('active');
            $t->timestamps();
        });

        Schema::create('club_sport', function (Blueprint $t) {
            $t->foreignId('club_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $t->primary(['club_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_sport');
        Schema::dropIfExists('sports');
    }
};
