<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brigade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrigadesController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $query = Brigade::query()->orderBy('brigade_id', 'desc');

        if ($q !== '') {
            $query->where('name', 'ilike', "%{$q}%");
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('.brigades', 'name')],
        ]);

        return response()->json(['data' => Brigade::create($data)], 201);
    }

    public function update(Request $request, int $id)
    {
        $b = Brigade::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('.brigades', 'name')->ignore($b->brigade_id, 'brigade_id')],
        ]);

        $b->fill($data)->save();
        return response()->json(['data' => $b]);
    }

    public function destroy(int $id)
    {
        Brigade::findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }
}
