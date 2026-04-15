<?php

declare(strict_types=1);

namespace App\Modules\Users\Actions;

use App\Modules\Users\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class LogActivityAction
{
    public function execute(string $action, Model $subject, array $meta = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'subject_type' => $subject::class,
            'subject_id'   => $subject->getKey(),
            'meta'         => $meta,
            'ip_address'   => Request::ip(),
        ]);
    }
}
