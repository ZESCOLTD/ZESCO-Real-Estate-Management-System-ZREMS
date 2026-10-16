<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function isOracle(): bool
    {
        return in_array(DB::getDriverName(), ['oci8', 'oracle'], true);
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    public function up(): void
    {
        if (! Schema::hasTable('acquisition_requests')) {
            return;
        }

        // Convert boolean/tinyint(1) to enum-friendly tinyint so we can store YesNo values (1=Yes, 2=No)
        if ($this->isOracle()) {
            // ORA-01439: Oracle cannot change datatype of a populated column directly.
            // Use add-copy-drop-rename path to preserve existing data.
            if (! $this->hasColumn('acquisition_requests', 'is_budget_approved_tmp')) {
                DB::statement('ALTER TABLE acquisition_requests ADD is_budget_approved_tmp NUMBER(1) NULL');
            }

            DB::statement("
                UPDATE acquisition_requests
                SET is_budget_approved_tmp = CASE
                    WHEN is_budget_approved IS NULL THEN NULL
                    WHEN TRIM(TO_CHAR(is_budget_approved)) = '0' THEN 2
                    WHEN TRIM(TO_CHAR(is_budget_approved)) IN ('1', '2') THEN TO_NUMBER(TRIM(TO_CHAR(is_budget_approved)))
                    ELSE NULL
                END
            ");

            if ($this->hasColumn('acquisition_requests', 'is_budget_approved')) {
                DB::statement('ALTER TABLE acquisition_requests DROP COLUMN is_budget_approved');
            }

            DB::statement('ALTER TABLE acquisition_requests RENAME COLUMN is_budget_approved_tmp TO is_budget_approved');
        } else {
            // MySQL-compatible syntax for local dev
            DB::statement("ALTER TABLE acquisition_requests MODIFY is_budget_approved TINYINT UNSIGNED NULL DEFAULT NULL");

            // Map old boolean false (0) to enum No (2). Keep 1 as Yes.
            DB::table('acquisition_requests')
                ->where('is_budget_approved', 0)
                ->update(['is_budget_approved' => 2]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('acquisition_requests')) {
            return;
        }

        // Map enum No (2) back to false (0) for boolean column
        DB::table('acquisition_requests')
            ->where('is_budget_approved', 2)
            ->update(['is_budget_approved' => 0]);

        if ($this->isOracle()) {
            DB::statement("
                ALTER TABLE acquisition_requests
                MODIFY is_budget_approved NUMBER(1) DEFAULT 0 NOT NULL
            ");
        } else {
            // MySQL-compatible syntax for local dev
            DB::statement("ALTER TABLE acquisition_requests MODIFY is_budget_approved TINYINT(1) NOT NULL DEFAULT 0");
        }
    }
};
