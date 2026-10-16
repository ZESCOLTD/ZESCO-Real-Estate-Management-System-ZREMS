<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class ParcelDocument extends Model
{
    use SoftDeletes;

    protected $casts = [
        'file_size' => 'integer',
        'document_date' => 'date',
    ];

    public function landParcel(): BelongsTo
    {
        return $this->belongsTo(LandParcel::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
