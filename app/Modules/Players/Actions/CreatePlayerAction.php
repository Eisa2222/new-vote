<?php

declare(strict_types=1);

namespace App\Modules\Players\Actions;

use App\Modules\Players\Models\Player;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class CreatePlayerAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(array $data, ?UploadedFile $photo = null): Player
    {
        return DB::transaction(function () use ($data, $photo) {
            if ($photo) {
                $data['photo_path'] = $photo->store('players/photos', 'public');
            }
            $player = Player::create($data);
            $this->log->execute('players.created', $player);
            return $player->load(['club', 'sport']);
        });
    }
}
