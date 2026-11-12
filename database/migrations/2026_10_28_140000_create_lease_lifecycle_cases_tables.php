<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_lifecycle_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_agreement_id')->constrained()->restrictOnDelete();
            $table->string('case_type', 24);
            $table->string('status', 24)->default('draft');
            $table->text('reason')->nullable();
            $table->date('proposed_start_date')->nullable();
            $table->date('proposed_end_date')->nullable();
            $table->decimal('proposed_monthly_payment', 18, 2)->nullable();
            $table->date('proposed_effective_date')->nullable();
            $table->date('notice_date')->nullable();
            $table->json('details')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['lease_agreement_id', 'case_type', 'status'], 'lease_lifecycle_type_status_idx');
        });

        Schema::create('lease_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_lifecycle_case_id')
                ->constrained('lease_lifecycle_cases')
                ->restrictOnDelete();
            $table->string('action', 48);
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24);
            $table->text('comment')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['lease_lifecycle_case_id', 'occurred_at'], 'lease_lifecycle_case_event_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_lifecycle_events');
        Schema::dropIfExists('lease_lifecycle_cases');
    }
};