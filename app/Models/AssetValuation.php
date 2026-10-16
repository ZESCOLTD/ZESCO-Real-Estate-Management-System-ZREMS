<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetValuation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'valuation_amount' => 'decimal:2',
        'valuation_date' => 'date:Y-m-d',
        'depreciation_rate' => 'decimal:2',
        'net_book_value' => 'decimal:2',
    ];

    public function builtAsset(): BelongsTo
    {
        return $this->belongsTo(BuiltAsset::class);
    }
}
