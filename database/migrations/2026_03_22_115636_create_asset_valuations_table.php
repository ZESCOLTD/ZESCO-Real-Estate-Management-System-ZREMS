<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('built_asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('valuation_amount', 18, 2)->nullable();
            $table->date('valuation_date')->nullable();
            $table->string('valuation_method')->nullable();
            $table->decimal('depreciation_rate', 8, 2)->nullable();
            $table->decimal('net_book_value', 18, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_valuations');
    }
};
