<?php

declare(strict_types=1);

namespace App\Modules\Players\Actions;

use App\Modules\Players\Models\Player;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UpdatePlayerAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Player $player, array $data, ?UploadedFile $photo = null): Player
    {
        return DB::transaction(function () use ($player, $data, $photo) {
            if ($photo) {
                if ($player->photo_path) {
                    Storage::disk('public')->delete($player->photo_path);
                }
                $data['photo_path'] = $photo->store('players/photos', 'public');
            }
            $player->update($data);
            $this->log->execute('players.updated', $player, array_keys($data));
            return $player->fresh(['club', 'sport']);
        });
    }
}
