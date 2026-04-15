<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Enums;

enum ResultsVisibility: string
{
    case Hidden    = 'hidden';
    case Approved  = 'approved';   // approved internally, not yet public
    case Announced = 'announced';  // public
}
