<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requested_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Optional linkage (e.g., acquisitions, due diligence) for later use
            $table->string('context_type')->nullable()->index();
            $table->unsignedBigInteger('context_id')->nullable()->index();

            $table->text('notes')->nullable();

            $table->string('status')->default('pending_assignment')->index();

            $table->timestamps();
        });

        Schema::create('service_request_departments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();

            // Keyed by service_parents.department_code (trimmed)
            $table->string('department_code')->index();

            // Snapshot of configured department leadership at request time (optional)
            $table->foreignId('hod_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Officer chosen by HOD/subordinates
            $table->foreignId('assigned_to_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('assigned_at')->nullable();

            $table->string('status')->default('pending_assignment')->index();

            $table->timestamps();

            $table->unique(['service_request_id', 'department_code'], 'svc_req_dept_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_departments');
        Schema::dropIfExists('service_requests');
    }
};
