<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceDepartmentPerson extends Model
{
    protected $table = 'service_department_people';

    protected $fillable = [
        'department_code',
        'hod_user_id',
    ];

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_user_id');
    }
}
