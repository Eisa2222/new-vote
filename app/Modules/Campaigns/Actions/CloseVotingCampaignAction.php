<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Actions;

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Events\CampaignClosed;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Users\Actions\LogActivityAction;

final class CloseVotingCampaignAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Campaign $campaign, string $reason = 'manual'): Campaign
    {
        if (! $campaign->status->canTransitionTo(CampaignStatus::Closed)) {
            throw new \DomainException("Cannot close campaign from {$campaign->status->value}.");
        }

        $campaign->update(['status' => CampaignStatus::Closed->value]);
        $this->log->execute('campaigns.closed', $campaign, ['reason' => $reason]);
        event(new CampaignClosed($campaign, $reason));

        return $campaign->fresh();
    }
}
