<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parcel_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stakeholder_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // owner, leaseholder, chief, headperson, family, council, complainant
            $table->boolean('is_primary')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['land_parcel_id', 'stakeholder_id', 'role'], 'parcel_stakeholder_role_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_stakeholders');
    }
};
