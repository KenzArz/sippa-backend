<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PcaVariance extends Model
{
    use HasUuids;

    protected $fillable = [
        'component',
        'explained_variance',
        'cumulative_variance',
    ];

    protected $casts = [
        'component' => 'integer',
        'explained_variance' => 'decimal:10',
        'cumulative_variance' => 'decimal:10',
    ];
}
