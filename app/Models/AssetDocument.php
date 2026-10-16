<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class AssetDocument extends Model
{
    use SoftDeletes;

    protected $casts = [
        'file_size' => 'integer',
        'document_date' => 'date',
    ];
    protected $fillable = [
        'asset_id',
        'document_type',
        'title',
        'file_path',
        'mime_type'];

    public function assetParcel(): BelongsTo
    {
        return $this->belongsTo(assetParcel::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class,'document_type','id');
    }
}
