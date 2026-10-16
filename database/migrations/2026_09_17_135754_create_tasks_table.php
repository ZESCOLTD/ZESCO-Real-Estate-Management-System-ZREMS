<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('event_category_id')->index()->default(0);
            $table->string('color')->nullable();
            $table->string('reference')->nullable();
            $table->string('name', 900);
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();

            $table->dateTime('received_at')->nullable();
            $table->dateTime('replied_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->tinyInteger('should_display')->default(1);

            $table->integer('event_urgency_level_id')->index()->default(1);
            $table->tinyInteger('task_status')->default(0);

            $table->string('creator_man_no')->nullable();
            $table->string('creator_name')->nullable();
            $table->string('creator_directorate')->nullable();
            $table->string('creator_location')->nullable();
            $table->string('creator_functional_section')->nullable();
            $table->string('creator_position')->nullable();
            $table->string('creator_grade')->nullable();
            $table->string('creator_email')->nullable();
            $table->string('creator_comment', 500)->nullable();

            $table->string('responsible_man_no')->nullable();
            $table->string('responsible_name')->nullable();
            $table->string('responsible_directorate')->nullable();
            $table->string('responsible_location')->nullable();
            $table->string('responsible_functional_section')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_grade')->nullable();
            $table->string('responsible_email')->nullable();
            $table->string('responsible_comment', 500)->nullable();
            $table->text('action_taken')->nullable();

            $table->string('sub_responsible_man_no')->nullable();
            $table->string('sub_responsible_name')->nullable();
            $table->string('sub_responsible_directorate')->nullable();
            $table->string('sub_responsible_location')->nullable();
            $table->string('sub_responsible_functional_section')->nullable();
            $table->string('sub_responsible_position')->nullable();
            $table->string('sub_responsible_grade')->nullable();
            $table->string('sub_responsible_email')->nullable();
            $table->string('sub_responsible_comment', 500)->nullable();
            $table->text('sub_action_taken')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
