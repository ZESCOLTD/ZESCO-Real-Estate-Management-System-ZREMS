<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetRegisterConsolidation extends Model
{
    protected $fillable = [
        'reference_no',
        'notes',
        'created_by',
        'result_asset_register_id',
    ];

    public function sources(): HasMany
    {
        return $this->hasMany(AssetRegisterConsolidationSource::class, 'asset_register_consolidation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resultAsset(): BelongsTo
    {
        return $this->belongsTo(AssetRegister::class, 'result_asset_register_id');
    }
}
