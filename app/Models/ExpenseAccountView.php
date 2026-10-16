<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseAccountView extends Model
{
    protected $table = 'EXPENSE_ACCOUNTS_VIEW';

    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;
}
