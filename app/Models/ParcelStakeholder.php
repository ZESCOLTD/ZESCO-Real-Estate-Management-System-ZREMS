<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('parcel_stakeholders', incrementing: true, )]
#[Unguarded]
class ParcelStakeholder extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_primary' => 'boolean',
        'effective_from' => 'date:Y-m-d',
        'effective_to' => 'date:Y-m-d',
    ];
}
