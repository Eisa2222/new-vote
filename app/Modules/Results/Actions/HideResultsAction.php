<?php

declare(strict_types=1);

namespace App\Modules\Results\Actions;

use App\Modules\Campaigns\Enums\ResultsVisibility;
use App\Modules\Results\Enums\ResultStatus;
use App\Modules\Results\Models\CampaignResult;
use App\Modules\Users\Actions\LogActivityAction;

final class HideResultsAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(CampaignResult $result): CampaignResult
    {
        $result->update(['status' => ResultStatus::Hidden->value]);
        $result->campaign->update(['results_visibility' => ResultsVisibility::Hidden->value]);
        $this->log->execute('results.hidden', $result);
        return $result->fresh();
    }
}
