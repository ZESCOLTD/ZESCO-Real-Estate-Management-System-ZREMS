<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflow_instances') && Schema::hasColumn('workflow_instances', 'acquisition_request_id')) {
            // Allow polymorphic workflow instances (e.g., Due Diligence) to exist without this legacy key.
            DB::statement('ALTER TABLE `workflow_instances` MODIFY `acquisition_request_id` BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('workflow_actions') && Schema::hasColumn('workflow_actions', 'acquisition_request_id')) {
            // Allow polymorphic workflow actions (e.g., Due Diligence) to exist without this legacy key.
            DB::statement('ALTER TABLE `workflow_actions` MODIFY `acquisition_request_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Best-effort revert: keep columns nullable-safe in case existing rows have NULL.
        // If you must revert, you should backfill NULLs first.
    }
};
