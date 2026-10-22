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
        'business_unit',
        'cost_centre',
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
        'area',
        'sub_location',
        'life_in_months',
        'status',
        'reportline',
        'source_row',
        'last_import_job_id',
        'imported_at',
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
        'life_in_months' => 'integer',
        'asset_cost' => 'decimal:2',
        'original_asset_cost' => 'decimal:2',
        'net_book_value' => 'decimal:2',
        'deprn_reserve' => 'decimal:2',
        'ytd_deprn' => 'decimal:2',
        'source_row' => 'array',
        'imported_at' => 'datetime',
    ];

    public function consolidationSources(): HasMany
    {
        return $this->hasMany(AssetRegisterConsolidationSource::class, 'asset_register_id');
    }
}
