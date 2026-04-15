<?php

declare(strict_types=1);

namespace App\Modules\Players\Enums;

enum PlayerPosition: string
{
    case Attack     = 'attack';
    case Midfield   = 'midfield';
    case Defense    = 'defense';
    case Goalkeeper = 'goalkeeper';

    public function label(): string
    {
        return match ($this) {
            self::Attack     => __('Attack'),
            self::Midfield   => __('Midfield'),
            self::Defense    => __('Defense'),
            self::Goalkeeper => __('Goalkeeper'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
