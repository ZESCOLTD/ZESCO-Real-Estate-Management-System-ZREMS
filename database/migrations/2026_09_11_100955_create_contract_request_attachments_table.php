<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_request_attachments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('contract_request_id')
                ->constrained('contract_requests')
                ->cascadeOnDelete();

            /*
             |--------------------------------------------------------------------------
             | Document Classification
             |--------------------------------------------------------------------------
             */

            $table->string('document_type', 100);

            $table->string('document_title');

            /*
             |--------------------------------------------------------------------------
             | File Information
             |--------------------------------------------------------------------------
             */

            $table->string('original_name');

            $table->string('file_name');

            $table->string('file_path');

            $table->string('mime_type', 150)
                ->nullable();

            $table->unsignedBigInteger('file_size')
                ->nullable();

            /*
             |--------------------------------------------------------------------------
             | Upload Information
             |--------------------------------------------------------------------------
             */

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
             |--------------------------------------------------------------------------
             | Indexes
             |--------------------------------------------------------------------------
             */

            $table->index([
                'contract_request_id',
                'document_type',
            ], 'contract_request_attachments_request_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_request_attachments');
    }
};
