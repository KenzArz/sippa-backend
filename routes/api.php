<?php

use App\Http\Controllers\Api\ClusterController;
use App\Http\Controllers\Api\CommodityController;
use App\Http\Controllers\Api\ModelEvaluationController;
use App\Http\Controllers\Api\ProvinceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Clusters endpoints
Route::prefix('clusters')->group(function () {
    Route::get('/', [ClusterController::class, 'index']);
    Route::get('/summary', [ClusterController::class, 'summary']);
    Route::get('/{id}', [ClusterController::class, 'show']);
    Route::get('/{id}/top-commodities', [ClusterController::class, 'topCommodities']);
});

// Provinces endpoints
Route::prefix('provinces')->group(function () {
    Route::get('/', [ProvinceController::class, 'index']);
    Route::get('/map-data', [ProvinceController::class, 'mapData']);
    Route::get('/{id}', [ProvinceController::class, 'show']);
    Route::get('/{id}/top-commodities', [ProvinceController::class, 'topCommodities']);
    Route::get('/{id}/commodity-comparison', [ProvinceController::class, 'commodityComparison']);
});

// Commodities endpoints
Route::prefix('commodities')->group(function () {
    Route::get('/', [CommodityController::class, 'index']);
    Route::get('/categories', [CommodityController::class, 'categories']);
    Route::get('/{id}', [CommodityController::class, 'show']);
    Route::get('/{id}/top-provinces', [CommodityController::class, 'topProvinces']);
});

// Model Evaluation endpoints
Route::prefix('model-evaluation')->group(function () {
    Route::get('/', [ModelEvaluationController::class, 'index']);
    Route::get('/kmeans', [ModelEvaluationController::class, 'kmeans']);
    Route::get('/pca', [ModelEvaluationController::class, 'pca']);
});
