<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clubs\Actions\CreateClubAction;
use App\Modules\Clubs\Actions\DeleteClubAction;
use App\Modules\Clubs\Actions\UpdateClubAction;
use App\Modules\Clubs\Http\Requests\StoreClubRequest;
use App\Modules\Clubs\Http\Requests\UpdateClubRequest;
use App\Modules\Clubs\Http\Resources\ClubResource;
use App\Modules\Clubs\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClubController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Club::class);

        $clubs = Club::query()
            ->with('sports')
            ->search($request->string('q')->toString())
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'))
            ->paginate($request->integer('per_page', 15));

        return ClubResource::collection($clubs);
    }

    public function show(Club $club): ClubResource
    {
        $this->authorize('view', $club);
        return new ClubResource($club->load('sports'));
    }

    public function store(StoreClubRequest $request, CreateClubAction $action): ClubResource
    {
        $data = $request->validated();
        $club = $action->execute(
            data: \Illuminate\Support\Arr::except($data, ['logo', 'sport_ids']),
            logo: $request->file('logo'),
            sportIds: $data['sport_ids'] ?? [],
        );

        return new ClubResource($club);
    }

    public function update(UpdateClubRequest $request, Club $club, UpdateClubAction $action): ClubResource
    {
        $data = $request->validated();
        $club = $action->execute(
            club: $club,
            data: \Illuminate\Support\Arr::except($data, ['logo', 'sport_ids']),
            logo: $request->file('logo'),
            sportIds: $data['sport_ids'] ?? null,
        );

        return new ClubResource($club);
    }

    public function destroy(Club $club, DeleteClubAction $action): JsonResponse
    {
        $this->authorize('delete', $club);
        $action->execute($club);
        return response()->json(status: 204);
    }
}
