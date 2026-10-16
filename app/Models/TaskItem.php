<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TaskItem extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;
    protected $guarded = [];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
