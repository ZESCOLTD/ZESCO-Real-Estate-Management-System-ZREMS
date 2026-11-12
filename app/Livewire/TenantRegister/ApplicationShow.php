<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantOnboardingApplication;
use Livewire\Component;

class ApplicationShow extends Component
{
    public TenantOnboardingApplication $application;

    public function mount(TenantOnboardingApplication $application): void
    {
        $this->authorize('tenant_register.view');
        abort_unless(
            (int) $application->created_by === (int) auth()->id()
                || auth()->user()->can('tenant_register.update')
                || auth()->user()->can('tenant_register.verify'),
            403
        );

        $this->application = $application;
    }

    public function render()
    {
        $this->authorize('tenant_register.view');
        $this->application->load([
            'documents.uploader',
            'documents.reviewer',
            'workflowInstances.currentStep',
            'workflowInstances.actions.actor',
            'historyEvents.actor',
        ]);

        return view('livewire.tenant-register.application-show')
            ->layout('layouts.master', ['title' => $this->application->reference_no]);
    }
}