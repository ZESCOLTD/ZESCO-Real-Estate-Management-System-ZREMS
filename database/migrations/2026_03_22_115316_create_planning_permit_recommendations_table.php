<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('planning_permit_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('applicant')->nullable();
            $table->string('beneficiary_entity')->nullable();
            $table->string('municipal_authority')->nullable();
            $table->json('documents_attached')->nullable();
            $table->json('technical_reviews')->nullable();
            $table->string('outcome')->nullable();
            $table->text('conditions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_permit_recommendations');
    }
};
