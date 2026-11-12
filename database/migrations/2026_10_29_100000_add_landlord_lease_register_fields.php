<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            if (! Schema::hasColumn('lease_agreements', 'tenant_name')) {
                $table->string('tenant_name')->nullable();
            }
            if (! Schema::hasColumn('lease_agreements', 'tenant_contact_name')) {
                $table->string('tenant_contact_name')->nullable();
            }
            if (! Schema::hasColumn('lease_agreements', 'tenant_contact_phone')) {
                $table->string('tenant_contact_phone', 30)->nullable();
            }
            if (! Schema::hasColumn('lease_agreements', 'tenant_contact_email')) {
                $table->string('tenant_contact_email')->nullable();
            }
            if (! Schema::hasColumn('lease_agreements', 'payment_period')) {
                $table->string('payment_period', 24)->nullable();
            }
            if (! Schema::hasColumn('lease_agreements', 'payment_amount')) {
                $table->decimal('payment_amount', 18, 2)->nullable();
            }
        });

        Schema::table('lease_landlord_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('lease_landlord_applications', 'lease_agreement_id')) {
                $table->unsignedBigInteger('lease_agreement_id')->nullable();
            }
        });

        if (! Schema::hasIndex('lease_landlord_applications', 'lease_landlord_app_agreement_unique')) {
            Schema::table('lease_landlord_applications', function (Blueprint $table) {
                $table->unique('lease_agreement_id', 'lease_landlord_app_agreement_unique');
            });
        }

        if (! Schema::hasIndex('lease_landlord_applications', 'lease_landlord_app_agreement_fk')) {
            Schema::table('lease_landlord_applications', function (Blueprint $table) {
                $table->foreign('lease_agreement_id', 'lease_landlord_app_agreement_fk')
                    ->references('id')
                    ->on('lease_agreements')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('lease_landlord_applications', function (Blueprint $table) {
            if (Schema::hasColumn('lease_landlord_applications', 'lease_agreement_id')) {
                $table->dropForeign('lease_landlord_app_agreement_fk');
                $table->dropUnique('lease_landlord_app_agreement_unique');
                $table->dropColumn('lease_agreement_id');
            }
        });

        Schema::table('lease_agreements', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'tenant_name',
                'tenant_contact_name',
                'tenant_contact_phone',
                'tenant_contact_email',
                'payment_period',
                'payment_amount',
            ], fn (string $column) => Schema::hasColumn('lease_agreements', $column)));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};