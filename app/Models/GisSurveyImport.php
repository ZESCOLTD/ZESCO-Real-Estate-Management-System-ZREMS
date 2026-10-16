<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Unguarded;

#[Unguarded]
class GisSurveyImport extends Model
{
    protected $table = 'gis_survey_imports';

    protected $casts = [
        'uploaded_at'  => 'datetime',
        'point_count'  => 'integer',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(GisLand::class, 'gis_land_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
