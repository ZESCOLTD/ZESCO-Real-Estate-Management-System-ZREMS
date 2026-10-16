<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('built_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->constrained()->cascadeOnDelete();
            $table->string('asset_code')->unique();
            $table->string('asset_type'); // substation, tower, office block, warehouse, etc.
            $table->string('name');
            $table->string('classification')->nullable(); // generation, transmission, distribution
            $table->date('commissioning_date')->nullable();
            $table->json('engineering_specs')->nullable();
            $table->text('description')->nullable();
            $table->string('condition_status')->nullable();
            $table->date('replacement_due_date')->nullable();
            $table->decimal('replacement_cost', 18, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('built_assets');
    }
};
