<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRegisterConsolidationSource extends Model
{
    protected $fillable = [
        'asset_register_consolidation_id',
        'asset_register_id',
    ];

    public function consolidation(): BelongsTo
    {
        return $this->belongsTo(AssetRegisterConsolidation::class, 'asset_register_consolidation_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetRegister::class, 'asset_register_id');
    }
}
