<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class ComplianceRecord extends Model
{
    use SoftDeletes;

    protected $casts = [
        'issue_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
        'renewal_due_date' => 'date:Y-m-d',
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
