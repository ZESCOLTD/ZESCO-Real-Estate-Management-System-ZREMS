<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('land_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requesting_entity')->nullable();
            $table->string('beneficiary')->nullable();
            $table->text('purpose')->nullable();

            $table->string('chiefdom')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->decimal('area_hectares', 14, 4)->nullable();
            $table->text('coordinates')->nullable();

            $table->string('chief_name')->nullable();
            $table->boolean('chief_consent_granted')->default(false);
            $table->string('chief_signature_path')->nullable();
            $table->date('chief_consent_date')->nullable();

            $table->boolean('council_verified')->default(false);
            $table->string('council_signature_path')->nullable();
            $table->date('council_verification_date')->nullable();

            $table->boolean('forwarded_to_commissioner')->default(false);
            $table->string('commissioner_ref_no')->nullable();
            $table->date('forwarded_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('land_conversions');
    }
};
