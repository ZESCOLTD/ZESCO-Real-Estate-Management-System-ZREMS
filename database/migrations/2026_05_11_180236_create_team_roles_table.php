<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_roles', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('created_by_man_no')->nullable();
            $table->string('created_by_name')->nullable();
            $table->string('created_by_position')->nullable();

            $table->string('updated_by_man_no')->nullable();
            $table->string('updated_by_name')->nullable();
            $table->string('updated_by_position')->nullable();


            $table->timestamps();
            $table->softDeletes();
        });

        \App\Models\TeamRole::create(['name'=>"Property Lawyer"]);
        \App\Models\TeamRole::create(['name'=>"Planner"]);
        \App\Models\TeamRole::create(['name'=>"Surveyor"]);
        \App\Models\TeamRole::create(['name'=>"Wayleave"]);
        \App\Models\TeamRole::create(['name'=>"Geomatic"]);
        \App\Models\TeamRole::create(['name'=>"Valuation"]);
        \App\Models\TeamRole::create(['name'=>"Finance"]);
        \App\Models\TeamRole::create(['name'=>"User Department"]);
    }

    public function down(): void
    {
        Schema::dropIfExists('team_roles');
    }
};
