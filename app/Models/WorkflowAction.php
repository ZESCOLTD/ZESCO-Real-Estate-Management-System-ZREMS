<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class WorkflowAction extends Model
{
    /**
     * Cache of whether the underlying table has the legacy
     * `acquisition_request_id` column. Some environments (Oracle test/prod)
     * were migrated without it — polymorphic workflowable_id / _type replaced it.
     */
    protected static ?bool $hasLegacyAcqCol = null;

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (self::$hasLegacyAcqCol === null) {
                // Oracle driver: assume the legacy column is NOT there
                // (production/test schemas were migrated polymorphic-only).
                // This avoids a Schema::hasColumn round-trip that may be
                // case-sensitive or unreliable on oci8.
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
        'workflow_instance_id',

        // legacy key (kept for backwards compatibility)
        'acquisition_request_id',

        // polymorphic fields
        'workflowable_id',
        'workflowable_type',

        'workflow_step_id',
        'step_order',
        'step_name',
        'action',
        'comment',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function workflowable()
    {
        return $this->morphTo();
    }

    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
