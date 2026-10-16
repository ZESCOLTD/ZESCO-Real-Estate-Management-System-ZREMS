<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class WorkflowInstance extends Model
{
    /**
     * Cache of whether the underlying table has the legacy
     * `acquisition_request_id` column. Oracle test/prod was migrated without it.
     */
    protected static ?bool $hasLegacyAcqCol = null;

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (self::$hasLegacyAcqCol === null) {
                $driver = $model->getConnection()->getDriverName();
                if ($driver === 'oracle' || $driver === 'oci') {
                    self::$hasLegacyAcqCol = false;
                } else {
                    try {
                        self::$hasLegacyAcqCol = Schema::hasColumn(
                            (new self)->getTable(),
                            'acquisition_request_id'
                        );
                    } catch (\Throwable $e) {
                        self::$hasLegacyAcqCol = false;
                    }
                }
            }

            if (! self::$hasLegacyAcqCol) {
                unset($model->attributes['acquisition_request_id']);
            }
        });
    }
    protected $fillable = [
        'workflow_definition_id',

        // legacy key (kept for backwards compatibility)
        'acquisition_request_id',

        // ✅ polymorphic keys
        'workflowable_id',
        'workflowable_type',

        'current_step_id',
        'current_step_order',
        'status',
        'initiated_by',
        'initiated_at',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workflowDefinition()
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }

    // ✅ THIS is now the correct relationship
    public function workflowable()
    {
        return $this->morphTo();
    }

    public function currentStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function actions()
    {
        return $this->hasMany(WorkflowAction::class);
    }
    public function workflowActions()
    {
        return $this->morphMany(WorkflowAction::class, 'workflowable')
            ->orderBy('step_order');
    }

}
