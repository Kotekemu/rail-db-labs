<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brigade;
use App\Models\Position;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainType;

class LookupsController extends Controller
{
    public function stations()
    {
        return response()->json(['data' => Station::query()->orderBy('name')->get()]);
    }

    public function positions()
    {
        return response()->json(['data' => Position::query()->orderBy('name')->get()]);
    }

    public function brigades()
    {
        return response()->json(['data' => Brigade::query()->orderBy('name')->get()]);
    }

    public function trainTypes()
    {
        return response()->json(['data' => TrainType::query()->orderBy('name')->get()]);
    }

    public function trains()
    {
        return response()->json([
            'data' => Train::query()
                ->with(['type'])
                ->orderBy('train_number')
                ->get(),
        ]);
    }
}
