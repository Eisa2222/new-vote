<?php

declare(strict_types=1);

namespace App\Modules\Shared\Concerns;

/**
 * Picks the correct translated column (name_ar / name_en) based on app locale.
 * Kept deliberately tiny — no package dependency.
 */
trait HasTranslations
{
    public function localized(string $field): ?string
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        return $this->{"{$field}_{$locale}"} ?? $this->{"{$field}_en"} ?? $this->{"{$field}_ar"};
    }
}
