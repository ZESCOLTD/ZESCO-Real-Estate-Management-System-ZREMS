<?php

namespace App\Console\Commands;

use App\Models\AcquisitionRequest;
use Illuminate\Console\Command;

class CheckRequestWorkflow extends Command
{
    protected $signature = 'check:request {reference}';
    protected $description = 'Check workflow state for a request';

    public function handle()
    {
        $ref = $this->argument('reference');
        $req = AcquisitionRequest::with([
            'workflowInstance.currentStep.approvers',
            'workflowInstance.workflowDefinition.steps.approvers'
        ])->where('reference_no', $ref)->first();

        if (!$req) {
            $this->error("Request not found: $ref");
            return;
        }

        $this->info("=== Request: {$req->reference_no} ===");
        $this->line("Status: " . ($req->status?->label() ?? 'NULL'));
        $this->line("Created: {$req->created_at}");

        if (!$req->workflowInstance) {
            $this->warn("NO WORKFLOW INSTANCE!");
            return;
        }

        $currentStepId = $req->workflowInstance->current_step_id;
        $this->line("\n📍 Current Step ID: $currentStepId");
        
        $current = $req->workflowInstance->currentStep;
        if ($current) {
            $this->line("📌 Step Name: {$current->step_name} (Order: {$current->step_order})");
            $this->line("   Approvers:");
            foreach ($current->approvers as $approver) {
                $this->line("     - {$approver->name} (ID: {$approver->id})");
            }
        }

        $this->line("\n📊 All Workflow Steps:");
        foreach ($req->workflowInstance->workflowDefinition->steps->sortBy('step_order') as $step) {
            $marker = $step->id == $currentStepId ? ' ← CURRENT' : '';
            $this->line("   {$step->step_order}. {$step->step_name}$marker");
        }
    }
}
