<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementRequestAttachment extends Model
{
    protected $fillable = [
        'management_request_id',
        'name',
        'file_path',
    ];

    public function request()
    {
        return $this->belongsTo(ManagementRequest::class, 'management_request_id');
    }
}
