<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wayleaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->constrained()->cascadeOnDelete();
            $table->string('reference_no')->nullable()->unique();
            $table->string('type')->nullable(); // easement, wayleave, servitude
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('annual_rent', 18, 2)->nullable();
            $table->string('status')->default('active');
            $table->text('conditions')->nullable();
            $table->text('compensation_details')->nullable();
            $table->timestamps();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayleaves');
    }
};
