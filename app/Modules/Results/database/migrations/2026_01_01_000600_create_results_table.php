<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_results', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $t->enum('status', [
                'pending_calculation', 'calculated', 'approved', 'hidden', 'announced',
            ])->default('pending_calculation')->index();
            $t->timestamp('calculated_at')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('announced_at')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->unique('campaign_id');
        });

        Schema::create('result_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_result_id')->constrained()->cascadeOnDelete();
            $t->foreignId('voting_category_id')->constrained()->cascadeOnDelete();
            $t->foreignId('candidate_id')->constrained('voting_category_candidates')->cascadeOnDelete();
            $t->unsignedInteger('votes_count')->default(0);
            $t->unsignedSmallInteger('rank')->default(0);
            $t->boolean('is_winner')->default(false);
            $t->timestamps();

            $t->index(['campaign_result_id', 'voting_category_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_items');
        Schema::dropIfExists('campaign_results');
    }
};
