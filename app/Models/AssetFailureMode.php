<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class AssetFailureMode extends Model
{
    use SoftDeletes;


    public function builtAsset(): BelongsTo
    {
        return $this->belongsTo(BuiltAsset::class);
    }
}
