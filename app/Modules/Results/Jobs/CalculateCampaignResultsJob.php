<?php

declare(strict_types=1);

namespace App\Modules\Results\Jobs;

use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Results\Actions\CalculateCampaignResultsAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CalculateCampaignResultsJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(public readonly Campaign $campaign) {}

    public function handle(CalculateCampaignResultsAction $action): void
    {
        $action->execute($this->campaign);
    }
}
