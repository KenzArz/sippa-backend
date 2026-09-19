<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use Illuminate\Http\JsonResponse;

class CommodityController extends Controller
{
    /**
     * Display a listing of all commodities
     * 
     * GET /api/commodities
     */
    public function index(): JsonResponse
    {
        $category = request('category');
        $year = request('year');

        $query = Commodity::query();

        if ($category) {
            $query->where('category', $category);
        }

        if ($year) {
            $query->where('reference_year', $year);
        }

        $commodities = $query->orderBy('category')
            ->orderBy('display_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $commodities,
        ]);
    }

    /**
     * Display the specified commodity with production data
     * 
     * GET /api/commodities/{id}
     */
    public function show(string $id): JsonResponse
    {
        $commodity = Commodity::with([
            'provinceProductions' => function ($query) {
                $query->with('province:id,name,cluster_id')
                    ->orderBy('produksi', 'desc');
            },
        ])->find($id);

        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Commodity not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $commodity,
        ]);
    }

    /**
     * Get commodity categories with counts
     * 
     * GET /api/commodities/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Commodity::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get top producing provinces for a commodity
     * 
     * GET /api/commodities/{id}/top-provinces?limit=10
     */
    public function topProvinces(string $id): JsonResponse
    {
        $limit = request('limit', 10);

        $commodity = Commodity::find($id);
        if (!$commodity) {
            return response()->json([
                'success' => false,
                'message' => 'Commodity not found',
            ], 404);
        }

        $topProvinces = $commodity->provinceProductions()
            ->with('province:id,name,cluster_id')
            ->orderBy('produksi', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProvinces,
        ]);
    }
}
