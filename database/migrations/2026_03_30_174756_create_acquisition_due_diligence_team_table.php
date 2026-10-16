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
        Schema::create('acquisition_due_diligence_team', function (Blueprint $table) {
            $table->id();

            $table->integer('acquisition_due_diligence_id');

            $table->integer('user_id');

            // Optional team workflow fields
            $table->string('role')->nullable(); // e.g. Property Officer, Geomatic Officer
            $table->boolean('signed_off')->default(false);
            $table->timestamp('signed_off_at')->nullable();
            $table->text('signoff_comment')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['acquisition_due_diligence_id', 'user_id'], 'due_diligence_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquisition_due_diligence_team');
    }
};
