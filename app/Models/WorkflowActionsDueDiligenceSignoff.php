<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowActionsDueDiligenceSignoff extends Model
{
    use HasFactory;

    protected $table = 'workflow_actions_due_diligence_signoffs';

    protected $fillable = [
        'acquisition_due_diligence_id',
        'action', // submitted, approved, reverted, rejected, updated_draft, resent
        'comment',
        'file_path',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    /**
     * The user who performed the action
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    /**
     * The related due diligence record
     */
    public function dueDiligence()
    {
        return $this->belongsTo(AcquisitionDueDiligence::class, 'acquisition_due_diligence_id');
    }
}
