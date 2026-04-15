<?php

declare(strict_types=1);

namespace App\Modules\Sports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sports\Http\Resources\SportResource;
use App\Modules\Sports\Models\Sport;

final class SportController extends Controller
{
    public function index()
    {
        return SportResource::collection(Sport::orderBy('name_en')->get());
    }
}
