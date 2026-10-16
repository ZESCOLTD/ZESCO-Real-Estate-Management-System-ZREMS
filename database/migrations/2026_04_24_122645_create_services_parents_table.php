<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('service_parents', function (Blueprint $table) {
            $table->id();

            $table->string('department_name');
            $table->string('department_code')->index();

            // main service group name (e.g. Site Planning, Cadastral Survey)
            $table->string('service_name');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_parents');
    }
};
