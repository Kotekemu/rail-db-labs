<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RoutesController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Route::query()->orderBy('route_id', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'route_code' => ['required', 'string', 'max:20', Rule::unique('.routes', 'route_code')],
            'owner_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'train_id' => ['required', 'integer', 'exists:trains,train_id'],
            'departure_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'arrival_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'brigade_id' => ['nullable', 'integer', 'exists:brigades,brigade_id'],
        ]);

        $route = Route::create($data);

        return response()->json(['data' => $route], 201);
    }

    public function show(int $id)
    {
        $route = Route::query()->with(['stops.station'])->findOrFail($id);
        return response()->json(['data' => $route]);
    }

    public function update(Request $request, int $id)
    {
        $route = Route::findOrFail($id);

        $data = $request->validate([
            'route_code' => ['required', 'string', 'max:20', Rule::unique('.routes', 'route_code')->ignore($route->route_id, 'route_id')],
            'owner_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'train_id' => ['required', 'integer', 'exists:trains,train_id'],
            'departure_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'arrival_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'brigade_id' => ['nullable', 'integer', 'exists:brigades,brigade_id'],
        ]);

        $route->fill($data)->save();

        return response()->json(['data' => $route]);
    }

    public function destroy(int $id)
    {
        $route = Route::findOrFail($id);
        $route->delete();

        return response()->json(['ok' => true]);
    }

    public function storeWithStops(Request $request)
    {
        $data = $request->validate([
            'route_code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('.routes', 'route_code')],
            'owner_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'train_id' => ['required', 'integer', 'exists:trains,train_id'],
            'departure_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'arrival_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'brigade_id' => ['nullable', 'integer', 'exists:brigades,brigade_id'],
            'stops' => ['required', 'array', 'min:2'],
            'stops.*.stop_no' => ['required', 'integer', 'min:1'],
            'stops.*.station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'stops.*.arrival_time' => ['nullable', 'date'],
            'stops.*.departure_time' => ['nullable', 'date'],
        ]);

        $route = DB::transaction(function () use ($data) {
            $routeId = DB::table('rail.routes')->insertGetId([
                'route_code' => $data['route_code'],
                'owner_station_id' => $data['owner_station_id'],
                'train_id' => $data['train_id'],
                'departure_station_id' => $data['departure_station_id'],
                'arrival_station_id' => $data['arrival_station_id'],
                'brigade_id' => $data['brigade_id'] ?? null,
            ], 'route_id');

            foreach ($data['stops'] as $s) {
                DB::table('rail.route_stops')->insert([
                    'route_id' => $routeId,
                    'stop_no' => $s['stop_no'],
                    'station_id' => $s['station_id'],
                    'arrival_time' => $s['arrival_time'] ?? null,
                    'departure_time' => $s['departure_time'] ?? null,
                ]);
            }

            return $routeId;
        });

        return response()->json(['data' => ['route_id' => $route]], 201);
    }
}
