<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        $query = Personnel::query()
            ->with(['station', 'position', 'brigade'])
            ->orderBy('person_id', 'desc');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'ilike', "%{$q}%")
                    ->orWhere('inn', 'ilike', "%{$q}%");
            });
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'position_id' => ['required', 'integer', 'exists:positions,position_id'],
            'brigade_id' => ['nullable', 'integer', 'exists:brigades,brigade_id'],

            'full_name' => ['required', 'string', 'max:255'],

            'inn' => [
                'nullable',
                'string',
                'max:12',
                'regex:/^(\d{10}|\d{12})$/',
                Rule::unique('personnel', 'inn'),
            ],

            'hire_date' => ['sometimes', 'date'],
            'fired_at' => ['nullable', 'date', 'after_or_equal:hire_date'],
        ]);

        $payload = [
            'station_id' => $data['station_id'],
            'position_id' => $data['position_id'],
            'brigade_id' => $data['brigade_id'] ?? null,
            'full_name' => $data['full_name'],
            'inn' => $data['inn'] ?? null,
        ];

        if (array_key_exists('hire_date', $data) && $data['hire_date'] !== null) {
            $payload['hire_date'] = $data['hire_date'];
        }
        if (array_key_exists('fired_at', $data)) {
            $payload['fired_at'] = $data['fired_at'];
        }

        $p = Personnel::create($payload)->load(['station', 'position', 'brigade']);

        return response()->json(['data' => $p], 201);
    }

    public function update(Request $request, int $id)
    {
        $p = Personnel::findOrFail($id);

        $data = $request->validate([
            'station_id' => ['required', 'integer', 'exists:stations,station_id'],
            'position_id' => ['required', 'integer', 'exists:positions,position_id'],
            'brigade_id' => ['nullable', 'integer', 'exists:brigades,brigade_id'],

            'full_name' => ['required', 'string', 'max:255'],

            'inn' => [
                'nullable',
                'string',
                'max:12',
                'regex:/^(\d{10}|\d{12})$/',
                Rule::unique('personnel', 'inn')->ignore($p->person_id, 'person_id'),
            ],

            'hire_date' => ['sometimes', 'date'],
            'fired_at' => ['nullable', 'date', 'after_or_equal:hire_date'],
        ]);

        $p->station_id = $data['station_id'];
        $p->position_id = $data['position_id'];
        $p->brigade_id = $data['brigade_id'] ?? null;
        $p->full_name = $data['full_name'];
        $p->inn = $data['inn'] ?? null;

        if (array_key_exists('hire_date', $data) && $data['hire_date'] !== null) {
            $p->hire_date = $data['hire_date'];
        }
        if (array_key_exists('fired_at', $data)) {
            $p->fired_at = $data['fired_at'];
        }

        $p->save();

        return response()->json(['data' => $p->load(['station', 'position', 'brigade'])]);
    }

    public function destroy(int $id)
    {
        Personnel::findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }
}
