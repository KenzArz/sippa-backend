<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvincePadiMonthlyProduction extends Model
{
    use HasUuids;

    protected $fillable = [
        'province_id',
        'month',
        'year',
        'luas_panen',
        'produksi',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'luas_panen' => 'decimal:3',
        'produksi' => 'decimal:3',
    ];

    /**
     * Get the province that owns the production
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
