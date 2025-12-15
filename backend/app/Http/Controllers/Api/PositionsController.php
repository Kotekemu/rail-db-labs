<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $query = Position::query()->orderBy('position_id', 'desc');

        if ($q !== '') {
            $query->where('name', 'ilike', "%{$q}%");
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('.positions', 'name')],
        ]);

        return response()->json(['data' => Position::create($data)], 201);
    }

    public function update(Request $request, int $id)
    {
        $pos = Position::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('.positions', 'name')->ignore($pos->position_id, 'position_id')],
        ]);

        $pos->fill($data)->save();
        return response()->json(['data' => $pos]);
    }

    public function destroy(int $id)
    {
        Position::findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }
}
