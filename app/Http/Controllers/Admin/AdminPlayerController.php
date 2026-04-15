<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Clubs\Models\Club;
use App\Modules\Players\Actions\CreatePlayerAction;
use App\Modules\Players\Actions\UpdatePlayerAction;
use App\Modules\Players\Enums\PlayerPosition;
use App\Modules\Players\Http\Requests\StorePlayerRequest;
use App\Modules\Players\Http\Requests\UpdatePlayerRequest;
use App\Modules\Players\Models\Player;
use App\Modules\Sports\Models\Sport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

final class AdminPlayerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Player::class);

        $players = Player::with(['club', 'sport'])
            ->when($request->filled('club_id'),  fn ($q) => $q->where('club_id',  $request->integer('club_id')))
            ->when($request->filled('position'), fn ($q) => $q->where('position', $request->string('position')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name_ar', 'like', $t)->orWhere('name_en', 'like', $t));
            })
            ->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.players.index', [
            'players'   => $players,
            'clubs'     => Club::orderBy('name_en')->get(),
            'positions' => PlayerPosition::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Player::class);
        return view('admin.players.form', [
            'player'    => new Player(),
            'clubs'     => Club::orderBy('name_en')->get(),
            'sports'    => Sport::orderBy('name_en')->get(),
            'positions' => PlayerPosition::cases(),
        ]);
    }

    public function store(StorePlayerRequest $r, CreatePlayerAction $a): RedirectResponse
    {
        $data = $r->validated();
        $a->execute(Arr::except($data, ['photo']), $r->file('photo'));
        return redirect('/admin/players')->with('success', __('Player created.'));
    }

    public function edit(Player $player): View
    {
        $this->authorize('update', $player);
        return view('admin.players.form', [
            'player'    => $player->load(['club', 'sport']),
            'clubs'     => Club::orderBy('name_en')->get(),
            'sports'    => Sport::orderBy('name_en')->get(),
            'positions' => PlayerPosition::cases(),
        ]);
    }

    public function update(UpdatePlayerRequest $r, Player $player, UpdatePlayerAction $a): RedirectResponse
    {
        $data = $r->validated();
        $a->execute($player, Arr::except($data, ['photo']), $r->file('photo'));
        return redirect('/admin/players')->with('success', __('Player updated.'));
    }

    public function destroy(Player $player): RedirectResponse
    {
        $this->authorize('delete', $player);
        $player->delete();
        return redirect('/admin/players')->with('success', __('Player deleted.'));
    }
}
