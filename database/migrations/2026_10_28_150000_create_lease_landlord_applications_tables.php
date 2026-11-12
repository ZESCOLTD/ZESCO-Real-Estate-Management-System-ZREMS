<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lease_landlord_applications')) {
            Schema::create('lease_landlord_applications', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no', 32)->nullable()->unique();
                $table->string('origin', 32);
                $table->string('status', 24)->default('draft');
                $table->string('external_tenant_name');
                $table->string('contact_name')->nullable();
                $table->string('contact_phone', 30)->nullable();
                $table->string('contact_email')->nullable();
                $table->string('property_reference')->nullable();
                $table->text('property_description');
                $table->date('proposed_start_date')->nullable();
                $table->date('proposed_end_date')->nullable();
                $table->decimal('proposed_monthly_rent', 18, 2)->nullable();
                $table->text('proposal_notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'origin'], 'lease_landlord_app_status_origin_idx');
            });
        }

        if (! Schema::hasTable('lease_landlord_application_events')) {
            Schema::create('lease_landlord_application_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lease_landlord_application_id');
                $table->string('action', 48);
                $table->string('from_status', 24)->nullable();
                $table->string('to_status', 24);
                $table->text('comment')->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('occurred_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasIndex('lease_landlord_application_events', 'lease_landlord_app_event_time_idx')) {
            Schema::table('lease_landlord_application_events', function (Blueprint $table) {
                $table->index(['lease_landlord_application_id', 'occurred_at'], 'lease_landlord_app_event_time_idx');
            });
        }

        Schema::table('lease_landlord_application_events', function (Blueprint $table) {
            $table->foreign('lease_landlord_application_id', 'lease_landlord_app_event_app_fk')
                ->references('id')
                ->on('lease_landlord_applications')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_landlord_application_events');
        Schema::dropIfExists('lease_landlord_applications');
    }
};