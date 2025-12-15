<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StationsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Station::query()->orderBy('station_id', 'desc');

        if ($q !== '') {
            $query->where('name', 'ilike', "%{$q}%")
                  ->orWhere('address', 'ilike', "%{$q}%")
                  ->orWhere('inn', 'ilike', "%{$q}%");
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'inn' => [
                'nullable',
                'string',
                'max:12',
                'regex:/^(\d{10}|\d{12})$/',
                Rule::unique('.stations', 'inn'),
            ],
            'address' => ['required', 'string'],
        ]);

        $station = Station::create($data);

        return response()->json(['data' => $station], 201);
    }

    public function show(int $id)
    {
        return response()->json(['data' => Station::findOrFail($id)]);
    }

    public function update(Request $request, int $id)
    {
        $station = Station::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'inn' => [
                'nullable',
                'string',
                'max:12',
                'regex:/^(\d{10}|\d{12})$/',
                Rule::unique('.stations', 'inn')->ignore($station->station_id, 'station_id'),
            ],
            'address' => ['required', 'string'],
        ]);

        $station->fill($data)->save();

        return response()->json(['data' => $station]);
    }

    public function destroy(int $id)
    {
        $station = Station::findOrFail($id);
        $station->delete();

        return response()->json(['ok' => true]);
    }
}
