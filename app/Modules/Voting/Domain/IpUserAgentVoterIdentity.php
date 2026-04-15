<?php

declare(strict_types=1);

namespace App\Modules\Voting\Domain;

use Illuminate\Http\Request;

final class IpUserAgentVoterIdentity implements VoterIdentityStrategy
{
    public function identify(Request $request, int $campaignId): string
    {
        return hash('sha256', implode('|', [
            $campaignId,
            (string) $request->ip(),
            (string) $request->userAgent(),
        ]));
    }
}
