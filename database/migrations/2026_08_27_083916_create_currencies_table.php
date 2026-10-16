<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            // ISO 4217
            $table->string('code', 3)->unique();
            $table->string('name');
            $table->string('symbol', 10)->nullable();

            // Number of decimal places normally used
            $table->unsignedTinyInteger('decimal_places')->default(2);

            // Optional ISO numeric currency code
            $table->string('numeric_code', 3)->nullable()->unique();

            // Currency commonly used in the system
            $table->boolean('is_default')->default(false);

            // Whether the currency can currently be selected/used
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
