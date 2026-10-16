<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_items', function (Blueprint $table) {
            $table->id();
            $table->integer('task_id')->index();

            $table->text('description')->nullable();
            $table->text('response')->nullable();

            $table->dateTime('received_at')->nullable();
            $table->dateTime('replied_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->integer('event_urgency_level_id')->index()->default(1);
            $table->tinyInteger('task_status')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_items');
    }
};
