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

// API Root - Health Check
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'SIPPA API is running',
        'version' => '1.0.0',
        'endpoints' => [
            'clusters' => [
                'GET /api/clusters',
                'GET /api/clusters/summary',
                'GET /api/clusters/{id}',
                'GET /api/clusters/{id}/top-commodities',
            ],
            'provinces' => [
                'GET /api/provinces',
                'GET /api/provinces/map-data',
                'GET /api/provinces/{id}',
                'GET /api/provinces/{id}/top-commodities',
                'GET /api/provinces/{id}/commodity-comparison',
            ],
            'commodities' => [
                'GET /api/commodities',
                'GET /api/commodities/categories',
                'GET /api/commodities/{id}',
                'GET /api/commodities/{id}/top-provinces',
            ],
            'model-evaluation' => [
                'GET /api/model-evaluation',
                'GET /api/model-evaluation/kmeans',
                'GET /api/model-evaluation/pca',
            ],
        ],
        'documentation' => 'See API_DOCUMENTATION.md',
    ]);
});

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

// Fallback route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
        'error' => 'The requested API endpoint does not exist',
        'available_endpoints' => [
            'GET /api/' => 'API health check and endpoint list',
            'GET /api/clusters' => 'List all clusters',
            'GET /api/provinces' => 'List all provinces',
            'GET /api/commodities' => 'List all commodities',
            'GET /api/model-evaluation' => 'Get model evaluation metrics',
        ],
        'documentation' => 'See API_DOCUMENTATION.md for complete reference',
    ], 404);
});
