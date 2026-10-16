<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('planning_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plot_area_ref')->nullable();
            $table->text('coordinates')->nullable();
            $table->string('requested_action'); // rezoning, use_change, subdivision, validation
            $table->string('intended_land_use')->nullable();
            $table->boolean('supporting_layout_attached')->default(false);
            $table->text('observations')->nullable();
            $table->string('recommendation')->nullable(); // approve, approve_with_conditions, reject
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_requisitions');
    }
};
