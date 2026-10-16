<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('acquisition_requests')) {
            return;
        }

        Schema::table('acquisition_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('acquisition_requests', 'project_accountant_user_id')) {
                $table->foreignId('project_accountant_user_id')
                    ->nullable()
                    ->after('workflow_stage')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('acquisition_requests', 'project_accountant_man_no')) {
                $table->string('project_accountant_man_no')->nullable()->after('project_accountant_user_id');
            }

            if (!Schema::hasColumn('acquisition_requests', 'project_accountant_name')) {
                $table->string('project_accountant_name')->nullable()->after('project_accountant_man_no');
            }

            if (!Schema::hasColumn('acquisition_requests', 'project_accountant_assigned_by')) {
                $table->foreignId('project_accountant_assigned_by')
                    ->nullable()
                    ->after('project_accountant_name')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('acquisition_requests', 'project_accountant_assigned_at')) {
                $table->timestamp('project_accountant_assigned_at')
                    ->nullable()
                    ->after('project_accountant_assigned_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('acquisition_requests')) {
            return;
        }

        Schema::table('acquisition_requests', function (Blueprint $table) {
            if (Schema::hasColumn('acquisition_requests', 'project_accountant_assigned_at')) {
                $table->dropColumn('project_accountant_assigned_at');
            }

            if (Schema::hasColumn('acquisition_requests', 'project_accountant_assigned_by')) {
                $table->dropConstrainedForeignId('project_accountant_assigned_by');
            }

            if (Schema::hasColumn('acquisition_requests', 'project_accountant_name')) {
                $table->dropColumn('project_accountant_name');
            }

            if (Schema::hasColumn('acquisition_requests', 'project_accountant_man_no')) {
                $table->dropColumn('project_accountant_man_no');
            }

            if (Schema::hasColumn('acquisition_requests', 'project_accountant_user_id')) {
                $table->dropConstrainedForeignId('project_accountant_user_id');
            }
        });
    }
};
