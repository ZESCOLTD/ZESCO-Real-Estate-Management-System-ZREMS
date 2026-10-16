<?php

use App\Models\AcquisitionRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function isOracle(): bool
    {
        return in_array(DB::getDriverName(), ['oci8', 'oracle'], true);
    }

    private function oracleHasIndexForColumnList(string $table, array $columns): bool
    {
        $table = strtoupper($table);
        $columns = array_map(static fn ($c) => strtoupper($c), $columns);
        $target = implode(',', $columns);

        $row = DB::selectOne(
            "
            SELECT COUNT(*) AS CNT
            FROM (
                SELECT index_name,
                       LISTAGG(column_name, ',') WITHIN GROUP (ORDER BY column_position) AS cols
                FROM user_ind_columns
                WHERE table_name = ?
                GROUP BY index_name
            ) x
            WHERE x.cols = ?
            ",
            [$table, $target]
        );

        return ((int) ($row->cnt ?? $row->CNT ?? 0)) > 0;
    }

    private function ensureMorphIndex(string $table, string $indexName): void
    {
        $columns = ['workflowable_type', 'workflowable_id'];

        if ($this->isOracle()) {
            // Avoid ORA-01408 when the same column list is already indexed
            // under another name.
            if (! $this->oracleHasIndexForColumnList($table, $columns)) {
                DB::statement(sprintf(
                    'CREATE INDEX %s ON %s (%s, %s)',
                    $indexName,
                    strtoupper($table),
                    strtoupper($columns[0]),
                    strtoupper($columns[1])
                ));
            }

            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
            try {
                $table->index($columns, $indexName);
            } catch (Throwable $e) {
                // ignore (index may already exist)
            }
        });
    }

    public function up(): void
    {
        if (Schema::hasTable('workflow_instances')) {
            Schema::table('workflow_instances', function (Blueprint $table) {
                if (!Schema::hasColumn('workflow_instances', 'workflowable_id')) {
                    $table->unsignedBigInteger('workflowable_id')->nullable()->after('workflow_definition_id');
                }

                if (!Schema::hasColumn('workflow_instances', 'workflowable_type')) {
                    $table->string('workflowable_type')->nullable()->after('workflowable_id');
                }
            });

            // Backfill from legacy acquisition_request_id when present
            if (Schema::hasColumn('workflow_instances', 'acquisition_request_id')) {
                $type = addslashes(AcquisitionRequest::class);
                DB::statement(
                    "UPDATE `workflow_instances`\n" .
                    "SET `workflowable_id` = `acquisition_request_id`, `workflowable_type` = '{$type}'\n" .
                    "WHERE `workflowable_id` IS NULL AND `acquisition_request_id` IS NOT NULL"
                );
            }

            $this->ensureMorphIndex('workflow_instances', 'workflow_instances_workflowable_index');
        }

        if (Schema::hasTable('workflow_actions')) {
            Schema::table('workflow_actions', function (Blueprint $table) {
                if (!Schema::hasColumn('workflow_actions', 'workflowable_id')) {
                    $table->unsignedBigInteger('workflowable_id')->nullable()->after('workflow_instance_id');
                }

                if (!Schema::hasColumn('workflow_actions', 'workflowable_type')) {
                    $table->string('workflowable_type')->nullable()->after('workflowable_id');
                }
            });

            // Backfill from legacy acquisition_request_id when present
            if (Schema::hasColumn('workflow_actions', 'acquisition_request_id')) {
                $type = addslashes(AcquisitionRequest::class);
                DB::statement(
                    "UPDATE `workflow_actions`\n" .
                    "SET `workflowable_id` = `acquisition_request_id`, `workflowable_type` = '{$type}'\n" .
                    "WHERE `workflowable_id` IS NULL AND `acquisition_request_id` IS NOT NULL"
                );
            }

            $this->ensureMorphIndex('workflow_actions', 'workflow_actions_workflowable_index');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_actions')) {
            Schema::table('workflow_actions', function (Blueprint $table) {
                if (Schema::hasColumn('workflow_actions', 'workflowable_type') && Schema::hasColumn('workflow_actions', 'workflowable_id')) {
                    try {
                        $table->dropIndex('workflow_actions_workflowable_index');
                    } catch (Throwable $e) {
                        // ignore
                    }
                }

                if (Schema::hasColumn('workflow_actions', 'workflowable_type')) {
                    $table->dropColumn('workflowable_type');
                }

                if (Schema::hasColumn('workflow_actions', 'workflowable_id')) {
                    $table->dropColumn('workflowable_id');
                }
            });
        }

        if (Schema::hasTable('workflow_instances')) {
            Schema::table('workflow_instances', function (Blueprint $table) {
                if (Schema::hasColumn('workflow_instances', 'workflowable_type') && Schema::hasColumn('workflow_instances', 'workflowable_id')) {
                    try {
                        $table->dropIndex('workflow_instances_workflowable_index');
                    } catch (Throwable $e) {
                        // ignore
                    }
                }

                if (Schema::hasColumn('workflow_instances', 'workflowable_type')) {
                    $table->dropColumn('workflowable_type');
                }

                if (Schema::hasColumn('workflow_instances', 'workflowable_id')) {
                    $table->dropColumn('workflowable_id');
                }
            });
        }
    }
};
