<?php

declare(strict_types=1);

namespace App\Modules\Players\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Players\Actions\CreatePlayerAction;
use App\Modules\Players\Actions\UpdatePlayerAction;
use App\Modules\Players\Http\Requests\StorePlayerRequest;
use App\Modules\Players\Http\Requests\UpdatePlayerRequest;
use App\Modules\Players\Http\Resources\PlayerResource;
use App\Modules\Players\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Player::class);

        $players = Player::query()
            ->with(['club', 'sport'])
            ->when($request->filled('club_id'),  fn ($q) => $q->where('club_id',  $request->integer('club_id')))
            ->when($request->filled('sport_id'), fn ($q) => $q->where('sport_id', $request->integer('sport_id')))
            ->when($request->filled('position'), fn ($q) => $q->where('position', $request->string('position')))
            ->when($request->filled('status'),   fn ($q) => $q->where('status',   $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name_ar', 'like', $t)->orWhere('name_en', 'like', $t));
            })
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'))
            ->paginate($request->integer('per_page', 20));

        return PlayerResource::collection($players);
    }

    public function show(Player $player): PlayerResource
    {
        $this->authorize('view', $player);
        return new PlayerResource($player->load(['club', 'sport']));
    }

    public function store(StorePlayerRequest $request, CreatePlayerAction $action): PlayerResource
    {
        $data   = $request->validated();
        $player = $action->execute(\Illuminate\Support\Arr::except($data, ['photo']), $request->file('photo'));
        return new PlayerResource($player);
    }

    public function update(UpdatePlayerRequest $request, Player $player, UpdatePlayerAction $action): PlayerResource
    {
        $data   = $request->validated();
        $player = $action->execute($player, \Illuminate\Support\Arr::except($data, ['photo']), $request->file('photo'));
        return new PlayerResource($player);
    }

    public function destroy(Player $player): JsonResponse
    {
        $this->authorize('delete', $player);
        $player->delete();
        return response()->json(status: 204);
    }
}
