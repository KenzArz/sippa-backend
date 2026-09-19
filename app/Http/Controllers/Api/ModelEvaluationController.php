<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KmeansEvaluation;
use App\Models\PcaVariance;
use Illuminate\Http\JsonResponse;

class ModelEvaluationController extends Controller
{
    /**
     * Get all K-Means evaluation metrics
     * 
     * GET /api/model-evaluation/kmeans
     */
    public function kmeans(): JsonResponse
    {
        $evaluations = KmeansEvaluation::orderBy('k')->get();

        return response()->json([
            'success' => true,
            'data' => $evaluations,
        ]);
    }

    /**
     * Get PCA variance explained data
     * 
     * GET /api/model-evaluation/pca
     */
    public function pca(): JsonResponse
    {
        $variances = PcaVariance::orderBy('component')->get();

        return response()->json([
            'success' => true,
            'data' => $variances,
        ]);
    }

    /**
     * Get complete model evaluation summary
     * 
     * GET /api/model-evaluation
     */
    public function index(): JsonResponse
    {
        $kmeans = KmeansEvaluation::orderBy('k')->get();
        $pca = PcaVariance::orderBy('component')->get();

        // K=4 is the FINAL CHOICE by ML team based on qualitative analysis
        // NOT purely statistical (K=2 has best Silhouette, but too general)
        // See README_ML.txt: "K=4 dipilih dengan mempertimbangkan Elbow Method, 
        // distribusi anggota cluster, profil multi-komoditas, dan interpretabilitas"
        $chosenK = 4;
        $chosenEvaluation = $kmeans->firstWhere('k', $chosenK);
        
        // Also get the statistically "best" K for comparison (K=2)
        $statisticalBestK = $kmeans->sortByDesc('silhouette_score')->first();

        // Get components explaining 90% variance
        $componentsFor90 = $pca->where('cumulative_variance', '>=', 0.90)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'kmeans' => [
                    'evaluations' => $kmeans,
                    'chosen_k' => $chosenK,
                    'chosen_k_metrics' => $chosenEvaluation,
                    'chosen_k_note' => 'K=4 dipilih berdasarkan analisis kualitatif (Elbow, distribusi cluster, interpretabilitas), bukan semata Silhouette Score tertinggi',
                    // For reference: statistical best (K=2 has highest Silhouette)
                    'statistical_best_k' => $statisticalBestK?->k,
                    'statistical_best_silhouette' => $statisticalBestK?->silhouette_score,
                ],
                'pca' => [
                    'variances' => $pca,
                    'components_for_90_percent' => $componentsFor90?->component,
                    'total_components' => $pca->count(),
                ],
            ],
        ]);
    }
}
