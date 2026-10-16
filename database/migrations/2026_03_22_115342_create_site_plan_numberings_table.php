<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_plan_numberings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requesting_entity')->nullable();
            $table->text('coordinates')->nullable();
            $table->decimal('area_hectares', 14, 4)->nullable();
            $table->string('use_type')->nullable();
            $table->string('requested_parcel_form')->nullable(); // plot, farm, lot, holding
            $table->string('assigned_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_plan_numberings');
    }
};
