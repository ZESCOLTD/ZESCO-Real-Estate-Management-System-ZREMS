<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceDepartmentGroupMember extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(ServiceDepartmentGroup::class, 'group_id');
    }
}
