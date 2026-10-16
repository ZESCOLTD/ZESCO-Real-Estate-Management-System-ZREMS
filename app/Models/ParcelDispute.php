<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class ParcelDispute extends Model
{
    use SoftDeletes;

    protected $casts = [
        'reported_at' => 'date:Y-m-d',
        'resolved_at' => 'date:Y-m-d',
    ];

    public function landParcel(): BelongsTo
    {
        return $this->belongsTo(LandParcel::class);
    }

    public function documents()
    {
        return $this->morphMany(ParcelDocument::class, 'documentable');
    }

}
