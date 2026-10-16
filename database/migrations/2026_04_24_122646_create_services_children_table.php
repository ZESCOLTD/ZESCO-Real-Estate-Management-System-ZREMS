<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('service_childrens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_parent_id')
                ->constrained('service_parents')
                ->cascadeOnDelete();

            // SERVICE DETAILS
            $table->string('service_name');

            // 🔥 NEW: MODEL AFFECTED
            $table->string('model')->nullable()->index();

            // 🔥 NEW: TYPE (controlled values)
            $table->enum('type', ['new_acquisition', 'management'])
                ->default('new_acquisition')
                ->index();

            // OPTIONAL: CODE
            $table->string('service_code')->nullable()->index();

            // STATUS
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            // 🔥 INDEXES FOR PERFORMANCE
            $table->index(['service_parent_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_childrens');
    }
};
