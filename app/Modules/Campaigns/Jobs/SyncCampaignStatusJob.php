<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Jobs;

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

final class SyncCampaignStatusJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(): void
    {
        // published -> active when start_at is reached
        Campaign::query()
            ->where('status', CampaignStatus::Published->value)
            ->where('start_at', '<=', now())
            ->where('end_at', '>', now())
            ->update(['status' => CampaignStatus::Active->value]);
    }
}
