<?php

declare(strict_types=1);

namespace App\Modules\Players\Actions;

use App\Modules\Players\Models\Player;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UploadPlayerPhotoAction
{
    public function execute(Player $player, UploadedFile $photo): Player
    {
        if ($player->photo_path) {
            Storage::disk('public')->delete($player->photo_path);
        }
        $player->update(['photo_path' => $photo->store('players/photos', 'public')]);
        return $player;
    }
}
