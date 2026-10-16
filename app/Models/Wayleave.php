<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class Wayleave extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
        'annual_rent' => 'decimal:2',
    ];

    public function landParcel(): BelongsTo
    {
        return $this->belongsTo(LandParcel::class);
    }

    public function documents()
    {
        return $this->morphMany(ParcelDocument::class, 'documentable');
    }

    public function payments()
    {
        return $this->morphMany(ParcelPayment::class, 'payable');
    }
}
