<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Actions;

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Users\Actions\LogActivityAction;

/**
 * "Activate now" = start voting immediately.
 *
 * Status becomes `active` AND start_at is pulled forward to now() if it
 * was in the future — otherwise the voter page would still return 410
 * (NOT_STARTED). If end_at already passed, it's pushed to +1 day so the
 * campaign is actually voteable.
 */
final class ActivateVotingCampaignAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Campaign $campaign): Campaign
    {
        if (! $campaign->status->canTransitionTo(CampaignStatus::Active)) {
            throw new \DomainException("Cannot activate campaign from {$campaign->status->value}.");
        }

        $update = ['status' => CampaignStatus::Active->value];
        if ($campaign->start_at->isFuture()) {
            $update['start_at'] = now();
        }
        if ($campaign->end_at->isPast()) {
            $update['end_at'] = now()->addDay();
        }

        $campaign->update($update);
        $this->log->execute('campaigns.activated', $campaign, [
            'start_moved' => isset($update['start_at']),
            'end_moved'   => isset($update['end_at']),
        ]);

        return $campaign->fresh();
    }
}
