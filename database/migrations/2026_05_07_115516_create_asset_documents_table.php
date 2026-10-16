<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('asset_id')->nullable();
//            $table->morphs('documentable');
            $table->nullableMorphs('documentable');
            $table->string('document_type'); // offer_letter, valuation_report, contract_of_sale, board_resolution, annexure_a, etc.
            $table->string('title');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('version')->nullable();
            $table->date('document_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_documents');
    }
};
