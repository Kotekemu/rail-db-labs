<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Http\Request;

class RouteStopsController extends Controller
{
    public function listByRoute(int $routeId)
    {
        $route = Route::findOrFail($routeId);

        $stops = RouteStop::query()
            ->with(['station'])
            ->where('route_id', $route->route_id)
            ->orderBy('stop_no')
            ->get();

        return response()->json(['data' => $stops]);
    }

    public function store(Request $request, int $routeId)
    {
        $route = Route::findOrFail($routeId);

        $data = $request->validate([
            'stop_no' => ['required', 'integer', 'min:1'],
            'station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'arrival_time' => ['nullable', 'date'],
            'departure_time' => ['nullable', 'date'],
        ]);

        $data['route_id'] = $route->route_id;

        $stop = RouteStop::create($data)->load(['station']);

        return response()->json(['data' => $stop], 201);
    }

    public function update(Request $request, int $stopId)
    {
        $stop = RouteStop::findOrFail($stopId);

        $data = $request->validate([
            'stop_no' => ['required', 'integer', 'min:1'],
            'station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'arrival_time' => ['nullable', 'date'],
            'departure_time' => ['nullable', 'date'],
        ]);

        $stop->fill($data)->save();

        return response()->json(['data' => $stop->load(['station'])]);
    }

    public function destroy(int $stopId)
    {
        $stop = RouteStop::findOrFail($stopId);
        $stop->delete();

        return response()->json(['ok' => true]);
    }
}
