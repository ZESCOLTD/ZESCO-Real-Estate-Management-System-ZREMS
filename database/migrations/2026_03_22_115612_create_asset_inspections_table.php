<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('built_asset_id')->constrained()->cascadeOnDelete();
            $table->date('inspection_date');
            $table->string('inspector_name')->nullable();
            $table->string('condition_rating')->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_inspections');
    }
};
