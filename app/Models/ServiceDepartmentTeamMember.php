<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceDepartmentTeamMember extends Model
{
    protected $fillable = [
        'department_code',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
