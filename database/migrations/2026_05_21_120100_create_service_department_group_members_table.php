<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_department_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                ->constrained('service_department_groups')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_id', 'user_id'], 'svc_dept_group_members_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_department_group_members');
    }
};
