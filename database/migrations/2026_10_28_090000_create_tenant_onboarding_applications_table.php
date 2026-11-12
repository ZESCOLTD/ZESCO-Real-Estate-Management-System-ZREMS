<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_onboarding_applications')) {
            Schema::create('tenant_onboarding_applications', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->nullable()->unique();
                $table->string('tenant_type')->nullable()->index();
                $table->string('applicant_name')->nullable()->index();
                $table->string('identity_reference')->nullable()->index();
                $table->string('man_no')->nullable()->index();
                $table->string('employer')->nullable();
                $table->string('organization_registration_no')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('physical_address')->nullable();
                $table->text('postal_address')->nullable();
                $table->string('station_location')->nullable()->index();
                $table->string('category')->nullable()->index();
                $table->string('location_code')->nullable();
                $table->string('requested_unit_reference')->nullable();
                $table->unsignedSmallInteger('occupants_count')->nullable();
                $table->date('requested_move_in_date')->nullable();
                $table->boolean('declaration_confirmed')->default(false);
                $table->string('status')->default('Draft')->index();
                $table->foreignId('tenant_register_id')->nullable()->constrained('tenant_registers')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('tenant_onboarding_documents')) {
            Schema::create('tenant_onboarding_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_onboarding_application_id')
                    ->constrained('tenant_onboarding_applications')
                    ->cascadeOnDelete();
                $table->string('document_type')->default('Supporting document');
                $table->string('original_name');
                $table->string('storage_path');
                $table->string('mime_type', 150)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('uploaded_at')->nullable();
                $table->timestamps();
            });
        } else {
            $hasApplicationForeignKey = collect(Schema::getForeignKeys('tenant_onboarding_documents'))
                ->contains(fn (array $foreignKey): bool =>
                    in_array('tenant_onboarding_application_id', $foreignKey['columns'], true)
                    && $foreignKey['foreign_table'] === 'tenant_onboarding_applications'
                );

            if (! $hasApplicationForeignKey) {
                Schema::table('tenant_onboarding_documents', function (Blueprint $table): void {
                    $table->foreign('tenant_onboarding_application_id', 'tenant_onboarding_documents_application_fk')
                        ->references('id')
                        ->on('tenant_onboarding_applications')
                        ->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_onboarding_documents');
        Schema::dropIfExists('tenant_onboarding_applications');
    }
};