<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('management_request_parent_services', function (Blueprint $table) {

            $table->id();

            $table->foreignId('request_department_id')
                ->constrained('management_request_depts')
                ->cascadeOnDelete();

            $table->foreignId('parent_service_id')
                ->constrained('service_parents')
                ->cascadeOnDelete();

            $table->string('budget_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('management_request_parent_services');
    }
};
