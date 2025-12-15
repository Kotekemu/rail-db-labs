<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainTypesController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $query = TrainType::query()->orderBy('train_type_id', 'desc');

        if ($q !== '') {
            $query->where('name', 'ilike', "%{$q}%");
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('.train_types', 'name')],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json(['data' => TrainType::create($data)], 201);
    }

    public function update(Request $request, int $id)
    {
        $t = TrainType::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('.train_types', 'name')->ignore($t->train_type_id, 'train_type_id')],
            'description' => ['nullable', 'string'],
        ]);

        $t->fill($data)->save();
        return response()->json(['data' => $t]);
    }

    public function destroy(int $id)
    {
        TrainType::findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }
}
