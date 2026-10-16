<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // e.g. Acquisition Request Approval
            $table->string('module'); // e.g. acquisition_request
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['module', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_definitions');
    }
};
