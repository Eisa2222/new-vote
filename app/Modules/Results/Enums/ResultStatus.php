<?php

declare(strict_types=1);

namespace App\Modules\Results\Enums;

enum ResultStatus: string
{
    case PendingCalculation = 'pending_calculation';
    case Calculated         = 'calculated';
    case Approved           = 'approved';
    case Hidden             = 'hidden';
    case Announced          = 'announced';
}
