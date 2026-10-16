<?php

namespace App\Models;

use App\Enums\Acquisition\AcquisitionType;
use App\Enums\Acquisition\ApprovalStatus;
use App\Enums\Land\OwnershipStatus;
use App\Enums\Land\Unit;
use App\Enums\Shared\YesNo;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class Province extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];


}
