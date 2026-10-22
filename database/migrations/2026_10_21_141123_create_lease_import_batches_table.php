<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('file_path');
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status', 24)->default('uploaded');
            $table->unsignedSmallInteger('row_count')->default(0);

            $table->json('rows')->nullable();
            $table->json('choices')->nullable();
            $table->json('errors')->nullable();
            $table->json('results')->nullable();

            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_import_batches');
    }
};