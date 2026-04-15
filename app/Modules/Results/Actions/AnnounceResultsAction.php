<?php

declare(strict_types=1);

namespace App\Modules\Results\Actions;

use App\Modules\Campaigns\Enums\ResultsVisibility;
use App\Modules\Results\Enums\ResultStatus;
use App\Modules\Results\Events\ResultsAnnounced;
use App\Modules\Results\Models\CampaignResult;
use App\Modules\Users\Actions\LogActivityAction;

final class AnnounceResultsAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(CampaignResult $result): CampaignResult
    {
        if ($result->status !== ResultStatus::Approved) {
            throw new \DomainException('Only approved results can be announced.');
        }

        $result->update([
            'status'       => ResultStatus::Announced->value,
            'announced_at' => now(),
        ]);
        $result->campaign->update(['results_visibility' => ResultsVisibility::Announced->value]);

        $this->log->execute('results.announced', $result);
        event(new ResultsAnnounced($result));

        return $result->fresh();
    }
}
