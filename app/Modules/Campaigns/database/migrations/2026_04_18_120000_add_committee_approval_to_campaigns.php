<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $t) {
            $t->timestamp('committee_approved_at')->nullable()->after('status');
            $t->foreignId('committee_approved_by')->nullable()->after('committee_approved_at')
                ->constrained('users')->nullOnDelete();
            $t->timestamp('committee_rejected_at')->nullable()->after('committee_approved_by');
            $t->foreignId('committee_rejected_by')->nullable()->after('committee_rejected_at')
                ->constrained('users')->nullOnDelete();
            $t->text('committee_rejection_note')->nullable()->after('committee_rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $t) {
            $t->dropConstrainedForeignId('committee_approved_by');
            $t->dropConstrainedForeignId('committee_rejected_by');
            $t->dropColumn([
                'committee_approved_at',
                'committee_rejected_at',
                'committee_rejection_note',
            ]);
        });
    }
};
