<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Events;

use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Foundation\Events\Dispatchable;

final class CampaignPublished
{
    use Dispatchable;

    public function __construct(public readonly Campaign $campaign) {}
}
