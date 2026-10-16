<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_form_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_child_id')->unique();
            $table->string('form_name');
            $table->text('description')->nullable();
            $table->json('sections')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_child_id')->references('id')->on('service_childrens')->onDelete('cascade');
        });

        Schema::create('service_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_form_template_id');
            $table->string('field_name');
            $table->string('field_label');
            $table->string('field_type');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('section')->nullable();
            $table->boolean('allow_comments')->default(false);
            $table->boolean('allow_attachment')->default(false);
            $table->boolean('allow_multiple_attachments')->default(false);
            $table->string('attachment_label')->nullable();
            $table->json('validation_rules')->nullable();
            $table->timestamps();

            $table->foreign('service_form_template_id')->references('id')->on('service_form_templates')->onDelete('cascade');
            $table->index(['service_form_template_id', 'sort_order'], 'sff_template_sort_idx');
        });

        Schema::create('service_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_service_id');
            $table->unsignedBigInteger('service_form_template_id');
            $table->unsignedBigInteger('submitted_by_id')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_request_service_id')->references('id')->on('service_request_services')->onDelete('cascade');
            $table->foreign('service_form_template_id')->references('id')->on('service_form_templates')->onDelete('cascade');
            $table->foreign('submitted_by_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['service_request_service_id', 'status'], 'sfs_request_status_idx');
        });

        Schema::create('service_form_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_form_submission_id');
            $table->unsignedBigInteger('service_form_field_id');
            $table->longText('value')->nullable();
            $table->text('comment')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->string('file_mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->timestamps();

            $table->foreign('service_form_submission_id')->references('id')->on('service_form_submissions')->onDelete('cascade');
            $table->foreign('service_form_field_id')->references('id')->on('service_form_fields')->onDelete('cascade');
            $table->index(['service_form_submission_id', 'service_form_field_id'], 'sfv_submission_field_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_form_values');
        Schema::dropIfExists('service_form_submissions');
        Schema::dropIfExists('service_form_fields');
        Schema::dropIfExists('service_form_templates');
    }
};
