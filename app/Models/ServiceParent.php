<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceParent extends Model
{
    protected $table = 'service_parents';
    protected $fillable = [
        'department_name',
        'department_code',
        'service_name',
    ];

    // ServiceParent.php
    public function children()
    {
        return $this->hasMany(ServiceChild::class, 'service_parent_id');
    }


}
