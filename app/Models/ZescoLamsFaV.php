<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZescoLamsFaV extends Model
{
    use HasFactory;

    protected $connection = 'fixed_asset_connection';
    protected $table = 'ZESCO_LAMS_FA_V';

    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;
}

