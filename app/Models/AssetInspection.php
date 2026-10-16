<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class AssetInspection extends Model
{
    use SoftDeletes;


    protected $casts = [
        'inspection_date' => 'date:Y-m-d',
    ];

    public function builtAsset(): BelongsTo
    {
        return $this->belongsTo(BuiltAsset::class);
    }
}
