<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('players', function (Blueprint $t) {
            $t->id();
            $t->foreignId('club_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $t->string('name_ar', 120);
            $t->string('name_en', 120);
            $t->string('photo_path')->nullable();
            $t->enum('position', ['attack', 'midfield', 'defense', 'goalkeeper'])->index();
            $t->boolean('is_captain')->default(false);
            $t->unsignedSmallInteger('jersey_number')->nullable();
            $t->enum('status', ['active', 'inactive'])->default('active')->index();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['club_id', 'sport_id']);
            $t->unique(['club_id', 'sport_id', 'jersey_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
