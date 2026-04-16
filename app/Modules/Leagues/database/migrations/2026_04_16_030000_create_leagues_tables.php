<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leagues', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $t->string('slug', 80)->unique();
            $t->string('name_ar', 150);
            $t->string('name_en', 150);
            $t->enum('status', ['active', 'inactive'])->default('active')->index();
            $t->timestamps();

            $t->unique(['sport_id', 'name_en']);
        });

        // Club ↔ League (a club can join multiple leagues across different sports)
        Schema::create('club_league', function (Blueprint $t) {
            $t->foreignId('club_id')->constrained()->cascadeOnDelete();
            $t->foreignId('league_id')->constrained()->cascadeOnDelete();
            $t->primary(['club_id', 'league_id']);
        });

        Schema::table('campaigns', function (Blueprint $t) {
            $t->foreignId('league_id')->nullable()->after('type')
                ->constrained('leagues')->nullOnDelete();
            $t->index('league_id');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $t) {
            $t->dropConstrainedForeignId('league_id');
        });
        Schema::dropIfExists('club_league');
        Schema::dropIfExists('leagues');
    }
};
