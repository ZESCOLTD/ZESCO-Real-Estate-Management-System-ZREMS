<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lease_tenant_workflows')) {
            Schema::create('lease_tenant_workflows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lease_agreement_id')->constrained()->restrictOnDelete();
                $table->string('department_name');
                $table->string('status', 48)->default('department_confirmation');
                $table->foreignId('initiated_by')->constrained('users')->restrictOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['lease_agreement_id', 'status'], 'lease_tenant_workflow_lease_status_idx');
            });
        }

        if (! Schema::hasTable('lease_tenant_workflow_events')) {
            Schema::create('lease_tenant_workflow_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lease_tenant_workflow_id')
                    ->constrained('lease_tenant_workflows')
                    ->restrictOnDelete();
                $table->string('action', 64);
                $table->string('from_status', 48)->nullable();
                $table->string('to_status', 48);
                $table->text('comment')->nullable();
                $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('acted_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasIndex('lease_tenant_workflow_events', 'lease_tenant_events_workflow_time_idx')) {
            Schema::table('lease_tenant_workflow_events', function (Blueprint $table) {
                $table->index(
                    ['lease_tenant_workflow_id', 'acted_at'],
                    'lease_tenant_events_workflow_time_idx'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_tenant_workflow_events');
        Schema::dropIfExists('lease_tenant_workflows');
    }
};