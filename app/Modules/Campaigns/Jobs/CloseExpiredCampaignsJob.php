<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Jobs;

use App\Modules\Campaigns\Actions\CloseVotingCampaignAction;
use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

final class CloseExpiredCampaignsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(CloseVotingCampaignAction $close): void
    {
        Campaign::query()
            ->whereIn('status', [CampaignStatus::Active->value, CampaignStatus::Published->value])
            ->where('end_at', '<=', now())
            ->get()
            ->each(fn (Campaign $c) => $close->execute($c, 'expired'));
    }
}
