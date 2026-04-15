<?php

declare(strict_types=1);

namespace App\Modules\Voting\Domain;

use Illuminate\Http\Request;

interface VoterIdentityStrategy
{
    /** Produces a stable identifier (<=128 chars) for duplicate prevention. */
    public function identify(Request $request, int $campaignId): string;
}
