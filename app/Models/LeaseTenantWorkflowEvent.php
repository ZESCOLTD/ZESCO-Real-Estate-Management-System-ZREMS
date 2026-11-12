<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseTenantWorkflowEvent extends Model
{
    protected $fillable = [
        'lease_tenant_workflow_id',
        'action',
        'from_status',
        'to_status',
        'comment',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(LeaseTenantWorkflow::class, 'lease_tenant_workflow_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}