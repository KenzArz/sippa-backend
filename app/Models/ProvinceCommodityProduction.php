<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvinceCommodityProduction extends Model
{
    use HasUuids;

    protected $fillable = [
        'province_id',
        'commodity_id',
        'produksi',
        'luas_panen',
        'produktivitas',
        'median_cluster',
        'selisih_dengan_median_cluster',
    ];

    protected $casts = [
        'produksi' => 'decimal:3',
        'luas_panen' => 'decimal:3',
        'produktivitas' => 'decimal:3',
        'median_cluster' => 'decimal:3',
        'selisih_dengan_median_cluster' => 'decimal:3',
    ];

    /**
     * Get the province that owns the production
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the commodity that owns the production
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }
}
