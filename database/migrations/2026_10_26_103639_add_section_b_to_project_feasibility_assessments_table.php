<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_feasibility_assessments', function (Blueprint $table) {
            $table->string('proposed_works_type')->nullable();
            $table->string('site_condition')->nullable();
            $table->string('existing_use')->nullable();
            $table->string('site_information_availability')->nullable();
            $table->text('key_existing_issue')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_feasibility_assessments', function (Blueprint $table) {
            $table->dropColumn([
                'proposed_works_type',
                'site_condition',
                'existing_use',
                'site_information_availability',
                'key_existing_issue',
            ]);
        });
    }
};