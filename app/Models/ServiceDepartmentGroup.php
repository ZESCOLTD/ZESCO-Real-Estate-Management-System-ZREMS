<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceDepartmentGroup extends Model
{
    protected $fillable = [
        'department_code',
        'name',
    ];

    public function members()
    {
        return $this->hasMany(ServiceDepartmentGroupMember::class, 'group_id');
    }
}
