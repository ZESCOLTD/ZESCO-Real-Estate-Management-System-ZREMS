<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcquisitionRequestAttachment extends Model
{
    protected $fillable = [
        'acquisition_request_id',
        'name',
        'file_path',
    ];

    public function request()
    {
        return $this->belongsTo(AcquisitionRequest::class, 'acquisition_request_id');
    }
}
