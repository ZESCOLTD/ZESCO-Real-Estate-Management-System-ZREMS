<?php

namespace App\Console\Commands;

use App\Enums\Acquisition\ApprovalStatus;
use App\Models\AcquisitionRequest;
use Illuminate\Console\Command;

class DiagnoseAcquisitionRequests extends Command
{
    protected $signature = 'diagnose:acquisition-requests {--date=today : Filter by date (today, yesterday, or YYYY-MM-DD)}';

    protected $description = 'Diagnose acquisition requests visibility issues';

    public function handle()
    {
        $dateFilter = $this->option('date');
        
        $query = AcquisitionRequest::with(['user', 'workflowInstance.currentStep.approvers']);

        if ($dateFilter === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', now()->subDay()->toDateString());
        } else {
            $query->whereDate('created_at', $dateFilter);
        }

        $requests = $query->latest()->limit(10)->get();

        if ($requests->isEmpty()) {
            $this->error("No requests found for: $dateFilter");
            return;
        }

        $this->info("=== Acquisition Requests for: $dateFilter ===\n");

        foreach ($requests as $req) {
            $this->line("📋 Reference: {$req->reference_no}");
            $this->line("   Project: {$req->project_name}");
            $this->line("   Applicant: {$req->user?->name}");
            $this->line("   Request Date: {$req->request_date}");
            $this->line("   Created At: {$req->created_at}");
            $statusLabel = $req->status?->label() ?? 'NULL';
            $statusValue = $req->status?->value ?? 'NULL';
            $this->line("   Status: $statusLabel ($statusValue)");
            
            if ($req->workflowInstance) {
                $stepName = $req->workflowInstance->currentStep?->step_name ?? 'N/A';
                $this->line("   Workflow Step: $stepName");
                $approvers = $req->workflowInstance->currentStep?->approvers ?? collect();
                if ($approvers->count()) {
                    $this->line("   Approvers:");
                    foreach ($approvers as $approver) {
                        $this->line("     - {$approver->name} (ID: {$approver->id})");
                    }
                } else {
                    $this->warn("   ⚠️  NO APPROVERS assigned to current step!");
                }
            } else {
                $this->warn("   ⚠️  NO WORKFLOW INSTANCE!");
            }

            $this->line('');
        }

        // Summary
        $this->info("=== Summary ===");
        $totalToday = AcquisitionRequest::whereDate('created_at', now()->toDateString())->count();
        $underReviewToday = AcquisitionRequest::whereDate('created_at', now()->toDateString())
            ->where('status', ApprovalStatus::UnderReview->value)
            ->count();
        $approvedToday = AcquisitionRequest::whereDate('created_at', now()->toDateString())
            ->where('status', ApprovalStatus::Approved->value)
            ->count();

        $this->line("Total requests TODAY: $totalToday");
        $this->line("Under Review today: $underReviewToday");
        $this->line("Approved today: $approvedToday");
    }
}
