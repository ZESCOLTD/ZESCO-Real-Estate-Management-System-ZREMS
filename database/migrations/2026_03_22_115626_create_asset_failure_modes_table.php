<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_failure_modes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('built_asset_id')->constrained()->cascadeOnDelete();
            $table->string('failure_mode');
            $table->text('description')->nullable();
            $table->string('severity')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_failure_modes');
    }
};
