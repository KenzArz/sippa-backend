<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cluster;
use Illuminate\Http\JsonResponse;

class ClusterController extends Controller
{
    /**
     * Display a listing of all clusters with summary info
     * 
     * GET /api/clusters
     */
    public function index(): JsonResponse
    {
        $clusters = Cluster::with(['provinces:id,name,cluster_id'])
            ->orderBy('cluster_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clusters,
        ]);
    }

    /**
     * Display the specified cluster with detailed commodity profiles
     * 
     * GET /api/clusters/{id}
     */
    public function show(string $id): JsonResponse
    {
        $cluster = Cluster::with([
            'provinces:id,name,cluster_id,total_production',
            'commodityProfiles' => function ($query) {
                $query->with('commodity:id,slug,display_name,category')
                    ->orderBy('mean_z_score', 'desc');
            },
        ])->find($id);

        if (!$cluster) {
            return response()->json([
                'success' => false,
                'message' => 'Cluster not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cluster,
        ]);
    }

    /**
     * Get cluster summary statistics
     * 
     * GET /api/clusters/summary
     */
    public function summary(): JsonResponse
    {
        $summary = Cluster::selectRaw('
            cluster_number,
            name,
            jumlah_provinsi,
            karakteristik
        ')
            ->orderBy('cluster_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get top commodities for a specific cluster
     * 
     * GET /api/clusters/{id}/top-commodities?limit=10
     */
    public function topCommodities(string $id): JsonResponse
    {
        $limit = request('limit', 10);

        $cluster = Cluster::find($id);
        if (!$cluster) {
            return response()->json([
                'success' => false,
                'message' => 'Cluster not found',
            ], 404);
        }

        $topCommodities = $cluster->commodityProfiles()
            ->with('commodity:id,slug,display_name,category')
            ->orderBy('mean_z_score', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topCommodities,
        ]);
    }
}
