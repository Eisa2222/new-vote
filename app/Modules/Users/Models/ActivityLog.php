<?php

declare(strict_types=1);

namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'meta', 'ip_address'];

    protected $casts = ['meta' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
