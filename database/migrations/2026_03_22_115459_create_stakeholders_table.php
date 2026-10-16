<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stakeholders', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // individual, chief, council, company, ministry, family_group
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('national_id')->nullable();
            $table->string('organization')->nullable();
            $table->string('designation')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stakeholders');
    }
};
