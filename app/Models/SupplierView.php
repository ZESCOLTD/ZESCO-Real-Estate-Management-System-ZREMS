<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierView extends Model
{
    protected $table = 'SUPPLIERS_VIEW';

    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;
}
