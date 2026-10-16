<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAllView extends Model
{
    use HasFactory;

    protected $connection = 'phris_connection';
    protected $table = 'PROJECTS_ALL_VIEW';

    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;
}

