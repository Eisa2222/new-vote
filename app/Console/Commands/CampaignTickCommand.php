<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Campaigns\Jobs\CloseExpiredCampaignsJob;
use App\Modules\Campaigns\Jobs\SyncCampaignStatusJob;
use Illuminate\Console\Command;

final class CampaignTickCommand extends Command
{
    protected $signature = 'campaigns:tick';
    protected $description = 'Sync campaign statuses (published -> active) and close expired campaigns';

    public function handle(): int
    {
        dispatch_sync(new SyncCampaignStatusJob());
        dispatch_sync(new CloseExpiredCampaignsJob());
        $this->info('Campaign tick done.');
        return self::SUCCESS;
    }
}
