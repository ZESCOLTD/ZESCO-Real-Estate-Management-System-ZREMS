<?php

namespace App\Services;

use App\Models\TenantHistoryEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TenantHistoryRecorder
{
    public function record(Model $tenantable, string $eventType, string $summary, array $details = []): TenantHistoryEvent
    {
        return TenantHistoryEvent::create([
            'tenantable_type' => $tenantable->getMorphClass(),
            'tenantable_id' => $tenantable->getKey(),
            'actor_id' => Auth::id(),
            'event_type' => $eventType,
            'summary' => $summary,
            'details' => $details,
            'occurred_at' => now(),
        ]);
    }
}