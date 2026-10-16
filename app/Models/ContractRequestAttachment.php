<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRequestAttachment extends Model
{
    use HasFactory;

    protected $fillable = [

        'contract_request_id',

        'document_type',

        'document_title',

        'original_name',

        'file_name',

        'file_path',

        'mime_type',

        'file_size',

        'uploaded_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Contract Request
    |--------------------------------------------------------------------------
    */

    public function contractRequest(): BelongsTo
    {
        return $this->belongsTo(
            ContractRequest::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Uploaded By
    |--------------------------------------------------------------------------
    */

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Human Readable File Size
    |--------------------------------------------------------------------------
    */

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '—';
        }

        $bytes = $this->file_size;

        if ($bytes >= 1024 * 1024) {
            return number_format(
                    $bytes / (1024 * 1024),
                    2
                ) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format(
                    $bytes / 1024,
                    2
                ) . ' KB';
        }

        return $bytes . ' B';
    }
}
