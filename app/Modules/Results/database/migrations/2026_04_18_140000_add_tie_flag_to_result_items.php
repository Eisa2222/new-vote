<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('result_items', function (Blueprint $t) {
            // Marked true when this item's votes tie with other candidates
            // right at the winners-cutoff, meaning the committee has to
            // manually pick which tied candidate(s) take the remaining slot(s).
            $t->boolean('needs_committee_decision')->default(false)->after('is_winner');
            $t->foreignId('committee_decided_by')->nullable()->after('needs_committee_decision')
                ->constrained('users')->nullOnDelete();
            $t->timestamp('committee_decided_at')->nullable()->after('committee_decided_by');
        });

        // is_winner must allow NULL so tied-at-cutoff items can be
        // "pending committee decision" rather than guessed.
        Schema::table('result_items', function (Blueprint $t) {
            $t->boolean('is_winner')->nullable()->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('result_items', function (Blueprint $t) {
            $t->dropConstrainedForeignId('committee_decided_by');
            $t->dropColumn(['needs_committee_decision', 'committee_decided_at']);
        });
    }
};
