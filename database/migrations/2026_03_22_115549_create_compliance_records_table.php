<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('compliance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_parcel_id')->constrained()->cascadeOnDelete();
            $table->string('compliance_type'); // environmental, planning, lease, social, dispute, permit
            $table->string('status')->default('pending');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('renewal_due_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_records');
    }
};
