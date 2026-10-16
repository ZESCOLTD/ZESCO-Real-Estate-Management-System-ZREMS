<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceChild extends Model
{
    protected $fillable = [
        'service_parent_id',
        'service_name',
        'model',
        'type'
    ];

    protected $table = 'service_childrens';
    public function parent()
    {
        return $this->belongsTo(ServiceParent::class, 'service_parent_id');
    }
}
