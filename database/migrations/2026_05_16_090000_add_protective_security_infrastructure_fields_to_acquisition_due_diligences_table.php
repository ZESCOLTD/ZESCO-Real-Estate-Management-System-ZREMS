<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acquisition_due_diligences', function (Blueprint $table) {
            // =============================
            // SECURITY: Protective Infrastructure Requirements
            // =============================
            $table->string('security_perimeter_fencing_required')->nullable();
            $table->json('security_fencing_types')->nullable();
            $table->string('security_fencing_other')->nullable();

            $table->string('security_guard_post_required')->nullable();
            $table->json('security_guard_post_types')->nullable();

            $table->json('security_infrastructure_stages')->nullable();
            $table->json('security_protective_measures')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('acquisition_due_diligences', function (Blueprint $table) {
            $table->dropColumn([
                'security_perimeter_fencing_required',
                'security_fencing_types',
                'security_fencing_other',
                'security_guard_post_required',
                'security_guard_post_types',
                'security_infrastructure_stages',
                'security_protective_measures',
            ]);
        });
    }
};
