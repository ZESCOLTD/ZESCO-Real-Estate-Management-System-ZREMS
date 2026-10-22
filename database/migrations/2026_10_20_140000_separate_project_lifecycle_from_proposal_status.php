<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('real_estate_projects', function (Blueprint $table) {
            $table->string('record_origin')->default('proposal');
            $table->string('lifecycle_status')->default('Proposed')->index();
            $table->string('proposal_status')->nullable()->index();
            $table->decimal('approved_budget', 18, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
        });

        DB::table('real_estate_projects')
            ->select(['id', 'status'])
            ->orderBy('id')
            ->chunkById(100, function ($projects): void {
                foreach ($projects as $project) {
                    $lifecycleStatus = match ($project->status) {
                        'Implementation' => 'Ongoing',
                        'Approved' => 'Planned',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                        default => 'Proposed',
                    };

                    DB::table('real_estate_projects')
                        ->where('id', $project->id)
                        ->update([
                            'proposal_status' => $project->status,
                            'lifecycle_status' => $lifecycleStatus,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('real_estate_projects', function (Blueprint $table) {
            $table->dropColumn([
                'record_origin',
                'lifecycle_status',
                'proposal_status',
                'approved_budget',
                'start_date',
                'expected_completion_date',
            ]);
        });
    }
};
