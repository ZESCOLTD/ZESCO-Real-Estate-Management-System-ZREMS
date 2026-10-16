<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acquisition_due_diligence_key_activities', function (Blueprint $table) {
            $table->id();

            $table->integer('acquisition_due_diligence_id');

            $table->string('name'); // e.g. Google Earth, Internal Listing Database
            $table->string('file_path')->nullable();
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->softDeletes();
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquisition_due_diligence_key_activities');
    }
};
