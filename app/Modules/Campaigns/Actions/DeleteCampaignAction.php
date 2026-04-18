<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Actions;

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

/**
 * Hard-deletes a campaign and every dependent row it owns
 * (categories, candidates, votes, vote items, result). Refuses to
 * delete a campaign that already has votes unless `$force` is true.
 * That keeps historical campaigns safe while allowing an admin to
 * clean up mistakes made in draft.
 */
final class DeleteCampaignAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Campaign $campaign, bool $force = false): void
    {
        $voteCount = $campaign->votes()->count();

        if ($voteCount > 0 && ! $force) {
            throw new \DomainException(
                __(':n vote(s) were cast — archive the campaign instead of deleting it, or use force-delete.', ['n' => $voteCount]),
            );
        }

        // Active or Published campaigns without votes are fine to drop (e.g.
        // admin created it by mistake). Archived/Closed/Rejected/Draft/Pending
        // are always droppable.
        DB::transaction(function () use ($campaign) {
            $this->log->execute('campaigns.deleted', $campaign, [
                'status' => $campaign->status->value,
                'votes'  => $campaign->votes()->count(),
            ]);

            // Cascading children — the DB has FK cascades on most, but we
            // delete explicitly to keep the transaction audit-friendly.
            $campaign->result()?->delete();
            foreach ($campaign->categories as $cat) {
                $cat->candidates()->delete();
                $cat->delete();
            }
            $campaign->votes()->each(function ($v) {
                $v->items()->delete();
                $v->delete();
            });
            $campaign->delete();
        });
    }
}
