<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Actions;

use App\Modules\Clubs\Models\Club;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class CreateClubAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(array $data, ?UploadedFile $logo = null, array $sportIds = []): Club
    {
        return DB::transaction(function () use ($data, $logo, $sportIds) {
            if ($logo) {
                $data['logo_path'] = $logo->store('clubs/logos', 'public');
            }

            $club = Club::create($data);

            if ($sportIds) {
                $club->sports()->sync($sportIds);
            }

            $this->log->execute('clubs.created', $club, ['name_en' => $club->name_en]);

            return $club->load('sports');
        });
    }
}
