<?php

declare(strict_types=1);

namespace App\Modules\Voting\Events;

use App\Modules\Voting\Models\Vote;
use Illuminate\Foundation\Events\Dispatchable;

final class VoteSubmitted
{
    use Dispatchable;

    public function __construct(public readonly Vote $vote) {}
}
