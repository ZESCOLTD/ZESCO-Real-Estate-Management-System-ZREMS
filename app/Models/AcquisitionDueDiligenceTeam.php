<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Unguarded]
class AcquisitionDueDiligenceTeam extends Model
{
    use SoftDeletes;

    protected $table = 'acquisition_due_diligence_team';

    protected $casts = [
        'acquisition_due_diligence_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function dueDiligence(): BelongsTo
    {
        return $this->belongsTo(AcquisitionDueDiligence::class, 'acquisition_due_diligence_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
