<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StationsController;
use App\Http\Controllers\Api\PersonnelController;
use App\Http\Controllers\Api\RoutesController;
use App\Http\Controllers\Api\RouteStopsController;
use App\Http\Controllers\Api\ViewsController;
use App\Http\Controllers\Api\LookupsController;
use App\Http\Controllers\Api\PositionsController;
use App\Http\Controllers\Api\BrigadesController;
use App\Http\Controllers\Api\TrainTypesController;
use App\Http\Controllers\Api\TrainsController;
use App\Http\Controllers\Api\ReportsController;

Route::get('/health', fn () => ['ok' => true]);

Route::get('/stations', [StationsController::class, 'index']);
Route::post('/stations', [StationsController::class, 'store']);
Route::get('/stations/{id}', [StationsController::class, 'show']);
Route::put('/stations/{id}', [StationsController::class, 'update']);
Route::delete('/stations/{id}', [StationsController::class, 'destroy']);

Route::get('/personnel', [PersonnelController::class, 'index']);
Route::post('/personnel', [PersonnelController::class, 'store']);
Route::put('/personnel/{id}', [PersonnelController::class, 'update']);
Route::delete('/personnel/{id}', [PersonnelController::class, 'destroy']);

Route::get('/routes', [RoutesController::class, 'index']);
Route::post('/routes', [RoutesController::class, 'store']);
Route::get('/routes/{id}', [RoutesController::class, 'show']);
Route::put('/routes/{id}', [RoutesController::class, 'update']);
Route::delete('/routes/{id}', [RoutesController::class, 'destroy']);

Route::get('/routes/{routeId}/stops', [RouteStopsController::class, 'listByRoute']);
Route::post('/routes/{routeId}/stops', [RouteStopsController::class, 'store']);
Route::put('/route-stops/{stopId}', [RouteStopsController::class, 'update']);
Route::delete('/route-stops/{stopId}', [RouteStopsController::class, 'destroy']);

Route::get('/views/route-overview', [ViewsController::class, 'routeOverview']);
Route::get('/views/busy-departure-stations', [ViewsController::class, 'busyDepartureStations']);

Route::get('/lookups/stations', [LookupsController::class, 'stations']);
Route::get('/lookups/positions', [LookupsController::class, 'positions']);
Route::get('/lookups/brigades', [LookupsController::class, 'brigades']);
Route::get('/lookups/train-types', [LookupsController::class, 'trainTypes']);
Route::get('/lookups/trains', [LookupsController::class, 'trains']);

Route::get('/positions', [PositionsController::class, 'index']);
Route::post('/positions', [PositionsController::class, 'store']);
Route::put('/positions/{id}', [PositionsController::class, 'update']);
Route::delete('/positions/{id}', [PositionsController::class, 'destroy']);

Route::get('/brigades', [BrigadesController::class, 'index']);
Route::post('/brigades', [BrigadesController::class, 'store']);
Route::put('/brigades/{id}', [BrigadesController::class, 'update']);
Route::delete('/brigades/{id}', [BrigadesController::class, 'destroy']);

Route::get('/train-types', [TrainTypesController::class, 'index']);
Route::post('/train-types', [TrainTypesController::class, 'store']);
Route::put('/train-types/{id}', [TrainTypesController::class, 'update']);
Route::delete('/train-types/{id}', [TrainTypesController::class, 'destroy']);

Route::get('/trains', [TrainsController::class, 'index']);
Route::post('/trains', [TrainsController::class, 'store']);
Route::put('/trains/{id}', [TrainsController::class, 'update']);
Route::delete('/trains/{id}', [TrainsController::class, 'destroy']);

Route::get('/reports/departures', [ReportsController::class, 'departures']);
Route::get('/reports/brigades', [ReportsController::class, 'brigades']);
Route::get('/reports/route-by-train-type', [ReportsController::class, 'routeByTrainType']);

Route::post('/routes-with-stops', [RoutesController::class, 'storeWithStops']);