<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Actions;

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;

/**
 * Committee rejects a pending campaign. Admin can then edit it and
 * resubmit for approval.
 */
final class RejectCampaignAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Campaign $campaign, ?string $reason = null): Campaign
    {
        if ($campaign->status !== CampaignStatus::PendingApproval) {
            throw new \DomainException(
                __('Only campaigns pending approval can be rejected.'),
            );
        }

        $campaign->update([
            'status'                   => CampaignStatus::Rejected->value,
            'committee_rejected_at'    => now(),
            'committee_rejected_by'    => Auth::id(),
            'committee_rejection_note' => $reason,
        ]);

        $this->log->execute('campaigns.rejected', $campaign, ['reason' => $reason]);

        return $campaign->fresh();
    }
}
