<?php

declare(strict_types=1);

namespace App\Modules\Voting\Providers;

use App\Modules\Voting\Domain\IpUserAgentVoterIdentity;
use App\Modules\Voting\Domain\VoterIdentityStrategy;
use Illuminate\Support\ServiceProvider;

final class VotingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VoterIdentityStrategy::class, IpUserAgentVoterIdentity::class);
    }
}
