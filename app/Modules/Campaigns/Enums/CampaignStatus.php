<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Enums;

enum CampaignStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Active    = 'active';
    case Closed    = 'closed';
    case Archived  = 'archived';

    /** Which transitions are legal. */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Draft     => in_array($next, [self::Published, self::Archived], true),
            self::Published => in_array($next, [self::Active, self::Closed, self::Archived], true),
            self::Active    => in_array($next, [self::Closed, self::Archived], true),
            self::Closed    => $next === self::Archived,
            self::Archived  => false,
        };
    }

    public function acceptsVotes(): bool
    {
        return $this === self::Active;
    }
}
