<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parcel_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->nullable()->constrained()->nullOnDelete();
//            $table->morphs('payable'); // acquisition request, valuation, invitation, rent, rates, etc.
            $table->nullableMorphs('payable');
            $table->string('payment_type'); // invitation_to_treat, ground_rent, property_rates, compensation
            $table->string('payee')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('budget_line')->nullable();
            $table->boolean('cet_board_approval')->default(false);
            $table->string('payment_mode')->nullable();
            $table->string('reference_no')->nullable();
            $table->date('payment_date')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_payments');
    }
};
