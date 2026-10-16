<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_department_team_members', function (Blueprint $table) {
            $table->id();

            $table->string('department_code')->index();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['department_code', 'user_id'], 'svc_dept_team_members_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_department_team_members');
    }
};
