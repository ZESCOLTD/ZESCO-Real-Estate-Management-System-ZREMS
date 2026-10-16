<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provincial_planning_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->nullable();
            $table->string('from_council')->nullable();
            $table->boolean('is_compliant')->default(false);
            $table->text('notes')->nullable();
            $table->date('notification_date')->nullable();
            $table->string('notification_ref_no')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provincial_planning_validations');
    }
};
