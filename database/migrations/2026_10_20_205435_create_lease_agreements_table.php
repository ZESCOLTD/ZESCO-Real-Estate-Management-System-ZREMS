<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_agreements', function (Blueprint $table) {
            $table->id();

            $table->text('contract_description');
            $table->string('landlord_name');
            $table->string('landlord_contact_phone', 30)->nullable();

            $table->string('town')->nullable();
            $table->foreignId('province_id')
                ->nullable()
                ->constrained('provinces')
                ->nullOnDelete();

            $table->string('lease_duration')->nullable();
            $table->decimal('lease_tenure_years', 6, 2)->nullable();

            $table->decimal('monthly_payment', 18, 2)->nullable();
            $table->decimal('annual_payment', 18, 2)->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->foreignId('lease_status_id')
                ->nullable()
                ->constrained('lease_statuses')
                ->restrictOnDelete();

            $table->text('comment')->nullable();

            $table->foreignId('responsible_officer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_agreements');
    }
};