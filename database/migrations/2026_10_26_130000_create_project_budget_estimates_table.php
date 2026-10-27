<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_budget_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('real_estate_project_id')->unique()->constrained('real_estate_projects')->cascadeOnDelete();
            $table->json('form_data');
            $table->decimal('itemized_subtotal', 18, 2)->nullable();
            $table->decimal('subtotal_before_contingency_tax', 18, 2)->nullable();
            $table->decimal('total_estimated_cost', 18, 2)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_budget_estimates');
    }
};
