<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class AssetPayment extends Model
{
    use SoftDeletes;

    protected $casts = [
        'amount' => 'decimal:2',
        'cet_board_approval' => 'boolean',
        'payment_date' => 'date:Y-m-d',
//        'attachments' => 'array',
    ];

    public function assetParcel(): BelongsTo
    {
        return $this->belongsTo(FmsAssetRegister::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
