<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Actions;

use App\Modules\Clubs\Models\Club;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UpdateClubAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Club $club, array $data, ?UploadedFile $logo = null, ?array $sportIds = null): Club
    {
        return DB::transaction(function () use ($club, $data, $logo, $sportIds) {
            if ($logo) {
                if ($club->logo_path) {
                    Storage::disk('public')->delete($club->logo_path);
                }
                $data['logo_path'] = $logo->store('clubs/logos', 'public');
            }

            $club->update($data);

            if ($sportIds !== null) {
                $club->sports()->sync($sportIds);
            }

            $this->log->execute('clubs.updated', $club, array_keys($data));

            return $club->fresh('sports');
        });
    }
}
