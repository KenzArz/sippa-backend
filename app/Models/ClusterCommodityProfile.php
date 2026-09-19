<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClusterCommodityProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'cluster_id',
        'commodity_id',
        'median_produksi',
        'mean_z_score',
    ];

    protected $casts = [
        'median_produksi' => 'decimal:3',
        'mean_z_score' => 'decimal:10',
    ];

    /**
     * Get the cluster that owns the profile
     */
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * Get the commodity that owns the profile
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }
}
