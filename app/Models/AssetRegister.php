<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetRegister extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'book_type_code',
        'asset_number',
        'asset_id',
        'tag_number',
        'date_placed_in_service',
        'asset_category',
        'major_category',
        'sub_major_category',
        'minor_category',
        'sub_minor_category',
        'description',
        'asset_cost',
        'original_asset_cost',
        'asset_location',
        'net_book_value',
        'deprn_reserve',
        'ytd_deprn',
        'asset_cost_acct',
        'asset_clearing_account',
        'reserve_account',
        'deprn_expense_account',
        'town',
        'location',
        'dept',
    ];

    protected $casts = [
        'date_placed_in_service' => 'date',
        'asset_cost' => 'decimal:2',
        'original_asset_cost' => 'decimal:2',
        'net_book_value' => 'decimal:2',
        'deprn_reserve' => 'decimal:2',
        'ytd_deprn' => 'decimal:2',
    ];

    public function consolidationSources(): HasMany
    {
        return $this->hasMany(AssetRegisterConsolidationSource::class, 'asset_register_id');
    }
}
