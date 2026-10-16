<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acquisition_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acquisition_request_id')
                ->constrained('acquisition_requests')
                ->onDelete('cascade');

            $table->string('name')->nullable();
            $table->string('file_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acquisition_request_attachments');
    }
};
