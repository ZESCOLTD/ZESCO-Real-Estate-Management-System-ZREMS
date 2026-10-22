<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_estate_projects', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->nullable()->unique();
            $table->string('project_title')->nullable();
            $table->string('project_category')->nullable();
            $table->string('property_name')->nullable();
            $table->string('property_reference')->nullable()->index();
            $table->string('town_location')->nullable();
            $table->string('responsible_officer')->nullable();
            $table->decimal('indicative_cost', 18, 2)->nullable();
            $table->string('status')->default('Draft')->index();
            $table->json('proposal_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('real_estate_projects');
    }
};
