<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parcel_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->constrained()->cascadeOnDelete();
            $table->string('dispute_type')->nullable();
            $table->string('status')->default('open');
            $table->text('description');
            $table->date('reported_at')->nullable();
            $table->date('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_disputes');
    }
};
