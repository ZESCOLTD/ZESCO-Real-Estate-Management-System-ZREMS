<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('title_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('beneficiary_entity')->nullable();
            $table->string('company_no')->nullable();
            $table->boolean('certificate_of_incorporation_attached')->default(false);
            $table->boolean('survey_diagram_attached')->default(false);
            $table->boolean('proof_of_payment_attached')->default(false);
            $table->string('requested_tenure')->nullable();
            $table->string('use_class')->nullable();
            $table->json('final_attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('title_requisitions');
    }
};
