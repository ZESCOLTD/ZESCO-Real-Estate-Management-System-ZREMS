<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceDepartmentSubordinate extends Model
{
    protected $table = 'service_department_subordinates';

    protected $fillable = [
        'department_code',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
