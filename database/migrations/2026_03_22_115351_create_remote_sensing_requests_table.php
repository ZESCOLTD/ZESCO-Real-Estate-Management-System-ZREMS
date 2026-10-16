<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('remote_sensing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('owner_name')->nullable();
            $table->string('contact')->nullable();
            $table->json('encumbrance_coverage')->nullable();
            $table->date('remedy_from')->nullable();
            $table->date('remedy_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remote_sensing_requests');
    }
};
