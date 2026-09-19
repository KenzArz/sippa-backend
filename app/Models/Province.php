<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'cluster_id',
        'geo_alias',
        'total_production',
    ];

    protected $casts = [
        'total_production' => 'decimal:2',
    ];

    /**
     * Get the cluster that owns the province
     */
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * Get commodity productions for this province
     */
    public function commodityProductions(): HasMany
    {
        return $this->hasMany(ProvinceCommodityProduction::class);
    }

    /**
     * Get padi monthly productions for this province
     */
    public function padiMonthlyProductions(): HasMany
    {
        return $this->hasMany(ProvincePadiMonthlyProduction::class);
    }
}
