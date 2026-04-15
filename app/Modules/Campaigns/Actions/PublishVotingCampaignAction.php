<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Actions;

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Events\CampaignPublished;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

final class PublishVotingCampaignAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Campaign $campaign): Campaign
    {
        if (! $campaign->status->canTransitionTo(CampaignStatus::Published)) {
            throw new \DomainException("Cannot publish campaign from {$campaign->status->value}.");
        }

        return DB::transaction(function () use ($campaign) {
            $next = now()->between($campaign->start_at, $campaign->end_at)
                ? CampaignStatus::Active
                : CampaignStatus::Published;

            $campaign->update(['status' => $next->value]);
            $this->log->execute('campaigns.published', $campaign);
            event(new CampaignPublished($campaign));
            return $campaign->fresh();
        });
    }
}
