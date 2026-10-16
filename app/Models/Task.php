<?php

namespace App\Models;

use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([TaskObserver::class])]
class Task extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start' => 'datetime',
            'end' => 'datetime',
            'received_at' => 'datetime',
            'replied_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function eventUrgencyLevel()
    {
        return $this->belongsTo(EventUrgencyLevel::class);
    }

    public function items()
    {
        return $this->hasMany(TaskItem::class);
    }
}
