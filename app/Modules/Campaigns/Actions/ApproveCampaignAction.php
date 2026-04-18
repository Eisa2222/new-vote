<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Actions;

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;

/**
 * Committee approves a pending campaign → it becomes Published.
 * The admin can then Activate it to open voting.
 */
final class ApproveCampaignAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Campaign $campaign): Campaign
    {
        if ($campaign->status !== CampaignStatus::PendingApproval) {
            throw new \DomainException(
                __('Only campaigns pending approval can be approved.'),
            );
        }

        $campaign->update([
            'status'                => CampaignStatus::Published->value,
            'committee_approved_at' => now(),
            'committee_approved_by' => Auth::id(),
        ]);

        $this->log->execute('campaigns.approved', $campaign);

        return $campaign->fresh();
    }
}
