<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_actions_due_diligence_signoffs', function (Blueprint $table) {
            $table->id();


            $table->integer('acquisition_due_diligence_id');

            $table->string('action');
            // submitted, approved, reverted, rejected, updated_draft, resent

            $table->text('comment');
            $table->text('file_path')->nullable();

            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();

            $table->timestamps();

            $table->index(['action']);
            $table->index(['acted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_actions_due_diligence_signoffs');
    }
};
