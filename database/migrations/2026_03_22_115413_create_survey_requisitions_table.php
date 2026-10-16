<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('survey_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('surveyor_name')->nullable();
            $table->string('license_no')->nullable();
            $table->json('required_products')->nullable();
            $table->boolean('submitted_to_survey_authority')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_requisitions');
    }
};
