<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\JsonResponse;

class ProvinceController extends Controller
{
    /**
     * Display a listing of all provinces with cluster info
     * 
     * GET /api/provinces
     */
    public function index(): JsonResponse
    {
        $provinces = Province::with([
            'cluster:id,cluster_number,name',
        ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $provinces,
        ]);
    }

    /**
     * Display the specified province with detailed commodity productions
     * 
     * GET /api/provinces/{id}
     */
    public function show(string $id): JsonResponse
    {
        $province = Province::with([
            'cluster:id,cluster_number,name,karakteristik',
            'commodityProductions' => function ($query) {
                $query->with('commodity:id,slug,display_name,category,reference_year')
                    ->orderBy('produksi', 'desc');
            },
        ])->find($id);

        if (!$province) {
            return response()->json([
                'success' => false,
                'message' => 'Province not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $province,
        ]);
    }

    /**
     * Get provinces grouped by cluster for map visualization
     * 
     * GET /api/provinces/map-data
     */
    public function mapData(): JsonResponse
    {
        $provinces = Province::with('cluster:id,cluster_number,name')
            ->get()
            ->map(function ($province) {
                return [
                    'id' => $province->id,
                    'name' => $province->name,
                    'geo_alias' => $province->geo_alias,
                    'cluster_number' => $province->cluster->cluster_number,
                    'cluster_name' => $province->cluster->name,
                    'total_production' => $province->total_production,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $provinces,
        ]);
    }

    /**
     * Get top commodities for a specific province
     * 
     * GET /api/provinces/{id}/top-commodities?limit=10
     */
    public function topCommodities(string $id): JsonResponse
    {
        $limit = request('limit', 10);

        $province = Province::find($id);
        if (!$province) {
            return response()->json([
                'success' => false,
                'message' => 'Province not found',
            ], 404);
        }

        $topCommodities = $province->commodityProductions()
            ->with('commodity:id,slug,display_name,category')
            ->orderBy('produksi', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topCommodities,
        ]);
    }

    /**
     * Get commodity production comparison with cluster median
     * 
     * GET /api/provinces/{id}/commodity-comparison
     */
    public function commodityComparison(string $id): JsonResponse
    {
        $province = Province::find($id);
        if (!$province) {
            return response()->json([
                'success' => false,
                'message' => 'Province not found',
            ], 404);
        }

        $comparison = $province->commodityProductions()
            ->with('commodity:id,slug,display_name,category')
            ->select([
                'commodity_id',
                'produksi',
                'median_cluster',
                'selisih_dengan_median_cluster',
            ])
            ->orderBy('selisih_dengan_median_cluster', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }
}
