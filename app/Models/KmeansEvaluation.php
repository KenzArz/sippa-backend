<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KmeansEvaluation extends Model
{
    use HasUuids;

    protected $fillable = [
        'k',
        'inertia',
        'silhouette_score',
        'davies_bouldin_index',
        'smallest_cluster',
        'largest_cluster',
    ];

    protected $casts = [
        'k' => 'integer',
        'inertia' => 'decimal:10',
        'silhouette_score' => 'decimal:8',
        'davies_bouldin_index' => 'decimal:8',
        'smallest_cluster' => 'integer',
        'largest_cluster' => 'integer',
    ];
}
