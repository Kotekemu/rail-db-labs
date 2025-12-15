<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusyDepartureStation;
use App\Models\RouteOverview;

class ViewsController extends Controller
{
    public function routeOverview()
    {
        return response()->json([
            'data' => RouteOverview::query()->orderBy('route_id', 'desc')->get(),
        ]);
    }

    public function busyDepartureStations()
    {
        return response()->json([
            'data' => BusyDepartureStation::query()->orderBy('routes_departing', 'desc')->get(),
        ]);
    }
}
