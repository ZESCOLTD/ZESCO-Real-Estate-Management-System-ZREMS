<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('geomatics_requests', function (Blueprint $table) {
            $table->id();

            // PART A
            $table->string('department');
            $table->date('requisition_date');

            $table->json('services');


            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('size')->nullable();
            $table->string('budget')->nullable();

            // PART B
            $table->string('supervisor')->nullable();
            $table->date('task_date')->nullable();
            $table->string('officer')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->string('sign_off')->nullable();

            // SYSTEM
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geomatics_requests');
    }
};
