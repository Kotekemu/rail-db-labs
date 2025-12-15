<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        $query = Train::query()
            ->with(['type', 'ownerStation'])
            ->orderBy('train_id', 'desc');

        if ($q !== '') {
            $query->where('train_number', 'ilike', "%{$q}%")
                  ->orWhere('name', 'ilike', "%{$q}%");
        }

        $data = $query->get()->map(function ($t) {
            return [
                'train_id' => $t->train_id,
                'owner_station_id' => $t->owner_station_id,
                'train_number' => $t->train_number,
                'train_type_id' => $t->train_type_id,
                'name' => $t->name,
                'type_name' => $t->type?->name,
                'owner_station_name' => $t->ownerStation?->name,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'train_number' => ['required', 'string', 'max:10', Rule::unique('.trains', 'train_number')],
            'train_type_id' => ['required', 'integer', 'exists:train_types,train_type_id'],
            'name' => ['nullable', 'string'],
        ]);

        return response()->json(['data' => Train::create($data)], 201);
    }

    public function update(Request $request, int $id)
    {
        $t = Train::findOrFail($id);

        $data = $request->validate([
            'owner_station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'train_number' => ['required', 'string', 'max:10', Rule::unique('.trains', 'train_number')->ignore($t->train_id, 'train_id')],
            'train_type_id' => ['required', 'integer', 'exists:train_types,train_type_id'],
            'name' => ['nullable', 'string'],
        ]);

        $t->fill($data)->save();
        return response()->json(['data' => $t]);
    }

    public function destroy(int $id)
    {
        Train::findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }
}
