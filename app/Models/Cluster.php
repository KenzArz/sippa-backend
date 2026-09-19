<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cluster extends Model
{
    use HasUuids;

    protected $fillable = [
        'cluster_number',
        'name',
        'jumlah_provinsi',
        'karakteristik',
    ];

    protected $casts = [
        'cluster_number' => 'integer',
        'jumlah_provinsi' => 'integer',
    ];

    /**
     * Get provinces in this cluster
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    /**
     * Get commodity profiles for this cluster
     */
    public function commodityProfiles(): HasMany
    {
        return $this->hasMany(ClusterCommodityProfile::class);
    }
}
