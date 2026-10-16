<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_request_department_id')
                ->constrained('service_request_departments')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('service_child_id')->nullable()->index();
            $table->string('service_name');

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

            $table->unique(['service_request_department_id', 'service_child_id'], 'svc_req_dept_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_services');
    }
};
