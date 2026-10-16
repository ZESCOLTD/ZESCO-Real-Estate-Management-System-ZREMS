<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_urgency_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('level')->default(0);
            $table->string('color')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        \App\Models\EventUrgencyLevel::create([
            'name' => 'Low / Minor (P4)',
            'description' => 'Cosmetic issues, nice-to-have features, or improvements.',
            'level' => 1,
            'color' => '#28a745', // Green
        ]);

        \App\Models\EventUrgencyLevel::create([
            'name' => 'Medium / Normal (P3)',
            'description' => 'Moderate impact, routine issues, or planned work.',
            'level' => 2,
            'color' => '#ffc107', // Yellow
        ]);

        \App\Models\EventUrgencyLevel::create([
            'name' => 'High / Urgent (P2)',
            'description' => 'Major impact but not a total shutdown. Needs fast resolution to prevent escalation.',
            'level' => 3,
            'color' => '#dc3545', // Red
        ]);

        \App\Models\EventUrgencyLevel::create([
            'name' => 'Critical / Emergency (P1)',
            'description' => 'System-down, life-threatening, or business-stopping issues.',
            'level' => 4,
            'color' => '#dc3545', // Red
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('event_urgency_levels');
    }
};
