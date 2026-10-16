<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_department_people', function (Blueprint $table) {
            $table->id();

            // Canonical department key (use TRIM(department_code) from service_parents)
            $table->string('department_code')->unique();

            $table->foreignId('hod_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('service_department_subordinates', function (Blueprint $table) {
            $table->id();

            $table->string('department_code')->index();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['department_code', 'user_id'], 'svc_dept_subordinates_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_department_subordinates');
        Schema::dropIfExists('service_department_people');
    }
};
