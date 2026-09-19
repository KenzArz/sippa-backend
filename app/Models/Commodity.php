<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commodity extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'display_name',
        'category',
        'reference_year',
        'unit',
        'has_luas_panen_produktivitas',
    ];

    protected $casts = [
        'reference_year' => 'integer',
        'has_luas_panen_produktivitas' => 'boolean',
    ];

    /**
     * Get cluster commodity profiles for this commodity
     */
    public function clusterProfiles(): HasMany
    {
        return $this->hasMany(ClusterCommodityProfile::class);
    }

    /**
     * Get province productions for this commodity
     */
    public function provinceProductions(): HasMany
    {
        return $this->hasMany(ProvinceCommodityProduction::class);
    }
}
