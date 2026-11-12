<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_expiry_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_agreement_id')->constrained()->restrictOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('days_before_end');
            $table->date('lease_end_date');
            $table->timestamp('queued_at');
            $table->timestamps();

            $table->unique(
                ['lease_agreement_id', 'days_before_end', 'lease_end_date'],
                'lease_expiry_notice_once_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_expiry_notification_logs');
    }
};