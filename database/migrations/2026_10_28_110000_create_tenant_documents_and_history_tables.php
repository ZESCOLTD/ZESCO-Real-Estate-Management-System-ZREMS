<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_documents')) {
            Schema::create('tenant_documents', function (Blueprint $table) {
                $table->id();
                $table->morphs('documentable');
                $table->string('document_type')->default('Supporting document');
                $table->string('original_name');
                $table->string('storage_path');
                $table->string('mime_type', 150)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('review_status')->default('Not Reviewed')->index();
                $table->date('expires_at')->nullable();
                $table->text('review_comment')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('uploaded_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('tenant_onboarding_documents')) {
            foreach (DB::table('tenant_onboarding_documents')->orderBy('id')->get() as $document) {
                $key = [
                    'documentable_type' => 'App\\Models\\TenantOnboardingApplication',
                    'documentable_id' => $document->tenant_onboarding_application_id,
                    'storage_path' => $document->storage_path,
                ];

                if (! DB::table('tenant_documents')->where($key)->exists()) {
                    DB::table('tenant_documents')->insert([
                        ...$key,
                        'document_type' => $document->document_type ?: 'Supporting document',
                        'original_name' => $document->original_name,
                        'mime_type' => $document->mime_type,
                        'file_size' => $document->file_size,
                        'review_status' => 'Not Reviewed',
                        'uploaded_by' => $document->uploaded_by,
                        'uploaded_at' => $document->uploaded_at,
                        'created_at' => $document->created_at,
                        'updated_at' => $document->updated_at,
                    ]);
                }
            }
        }

        if (! Schema::hasTable('tenant_history_events')) {
            Schema::create('tenant_history_events', function (Blueprint $table) {
                $table->id();
                $table->morphs('tenantable');
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event_type')->index();
                $table->string('summary', 500);
                $table->json('details')->nullable();
                $table->timestamp('occurred_at')->useCurrent();
                $table->index(['tenantable_type', 'tenantable_id', 'occurred_at'], 'tenant_history_timeline_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_history_events');
        Schema::dropIfExists('tenant_documents');
    }
};