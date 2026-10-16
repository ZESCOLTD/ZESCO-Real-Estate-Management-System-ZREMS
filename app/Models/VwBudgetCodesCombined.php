<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VwBudgetCodesCombined extends Model
{
    use HasFactory;

    protected $connection = 'budgets_connection';
    protected $table = 'VW_BUDGET_CODES_COMBINED';

    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;
}

