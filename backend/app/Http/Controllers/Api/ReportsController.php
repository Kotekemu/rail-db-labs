<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function departures(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'station_id' => ['nullable', 'integer'],
            'sort_by' => ['nullable', 'in:routes_count,avg_duration_min,total_duration_min,station_name'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ]);

        $sortBy = $validated['sort_by'] ?? 'routes_count';
        $sortDir = $validated['sort_dir'] ?? 'desc';

        $q = DB::table('rail.routes as r')
            ->join('rail.stations as s', 's.station_id', '=', 'r.departure_station_id')
            ->selectRaw('s.station_id, s.name as station_name')
            ->selectRaw('COUNT(r.route_id) as routes_count')
            ->selectRaw("AVG(EXTRACT(EPOCH FROM (r.scheduled_arrival - r.scheduled_departure))/60.0) as avg_duration_min")
            ->selectRaw("SUM(EXTRACT(EPOCH FROM (r.scheduled_arrival - r.scheduled_departure))/60.0) as total_duration_min")
            ->whereNotNull('r.scheduled_departure')
            ->whereNotNull('r.scheduled_arrival')
            ->groupBy('s.station_id', 's.name');

        if (!empty($validated['station_id'])) {
            $q->where('s.station_id', '=', $validated['station_id']);
        }
        if (!empty($validated['date_from'])) {
            $q->where('r.scheduled_departure', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $q->where('r.scheduled_departure', '<=', $validated['date_to']);
        }

        $rows = $q->orderBy($sortBy === 'station_name' ? 'station_name' : $sortBy, $sortDir)->get();

        $totals = [
            'total_routes' => (int) $rows->sum('routes_count'),
            'total_duration_min' => (float) $rows->sum('total_duration_min'),
            'avg_duration_min_overall' => (float) ($rows->count() ? $rows->avg('avg_duration_min') : 0),
        ];

        return response()->json([
            'filters' => $validated,
            'data' => $rows,
            'totals' => $totals,
        ]);
    }

    public function brigades(Request $request)
    {
        $validated = $request->validate([
            'station_id' => ['nullable', 'integer'],
            'position_id' => ['nullable', 'integer'],
            'only_active' => ['nullable', 'in:0,1'],
            'sort_by' => ['nullable', 'in:brigade_name,people_count,active_count,active_percent'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ]);

        $sortBy = $validated['sort_by'] ?? 'people_count';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $onlyActive = ($validated['only_active'] ?? '0') === '1';

        $q = DB::table('rail.brigades as b')
            ->leftJoin('rail.personnel as p', 'p.brigade_id', '=', 'b.brigade_id')
            ->selectRaw('b.brigade_id, b.name as brigade_name')
            ->selectRaw('COUNT(p.person_id) as people_count')
            ->selectRaw("SUM(CASE WHEN p.person_id IS NOT NULL AND p.fired_at IS NULL THEN 1 ELSE 0 END) as active_count")
            ->selectRaw("SUM(CASE WHEN p.person_id IS NOT NULL AND p.fired_at IS NOT NULL THEN 1 ELSE 0 END) as fired_count")
            ->groupBy('b.brigade_id', 'b.name');

        if (!empty($validated['station_id'])) {
            $q->where('p.station_id', '=', $validated['station_id']);
        }
        if (!empty($validated['position_id'])) {
            $q->where('p.position_id', '=', $validated['position_id']);
        }
        if ($onlyActive) {
            $q->whereNull('p.fired_at');
        }

        $rows = $q->get()->map(function ($r) {
            $people = (int) $r->people_count;
            $active = (int) $r->active_count;
            $r->active_percent = $people > 0 ? round(($active / $people) * 100, 2) : 0;
            return $r;
        });

        $rows = $rows->sortBy([
            [$sortBy, $sortDir === 'asc' ? SORT_ASC : SORT_DESC]
        ])->values();

        $totals = [
            'total_people' => (int) $rows->sum('people_count'),
            'total_active' => (int) $rows->sum('active_count'),
            'avg_active_percent' => (float) ($rows->count() ? $rows->avg('active_percent') : 0)
        ];

        return response()->json([
            'filters' => $validated,
            'data' => $rows,
            'totals' => $totals,
        ]);
    }

    public function routeByTrainType(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort_by' => ['nullable', 'in:train_type,routes_count,avg_duration_min'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ]);

        $sortBy = $validated['sort_by'] ?? 'routes_count';
        $sortDir = $validated['sort_dir'] ?? 'desc';

        $q = DB::table('rail.routes as r')
            ->join('rail.trains as t', 't.train_id', '=', 'r.train_id')
            ->join('rail.train_types as tt', 'tt.train_type_id', '=', 't.train_type_id')
            ->selectRaw('tt.train_type_id, tt.name as train_type')
            ->selectRaw('COUNT(r.route_id) as routes_count')
            ->selectRaw("AVG(EXTRACT(EPOCH FROM (r.scheduled_arrival - r.scheduled_departure))/60.0) as avg_duration_min")
            ->whereNotNull('r.scheduled_departure')
            ->whereNotNull('r.scheduled_arrival')
            ->groupBy('tt.train_type_id', 'tt.name');

        if (!empty($validated['date_from'])) {
            $q->where('r.scheduled_departure', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $q->where('r.scheduled_departure', '<=', $validated['date_to']);
        }

        $rows = $q->orderBy($sortBy, $sortDir)->get();

        $totals = [
            'total_routes' => (int) $rows->sum('routes_count'),
            'avg_duration_min_overall' => (float) ($rows->count() ? $rows->avg('avg_duration_min') : 0)
        ];

        return response()->json([
            'filters' => $validated,
            'data' => $rows,
            'totals' => $totals,
        ]);
    }
}
