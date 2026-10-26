<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_feasibility_assessments', function (Blueprint $table) {
            $table->decimal('preliminary_cost', 18, 2)->nullable();
            $table->boolean('cost_not_available')->default(false);
            $table->string('basis_of_estimate')->nullable();
            $table->string('funding_position')->nullable();
            $table->unsignedSmallInteger('implementation_months')->nullable();
            $table->boolean('period_not_established')->default(false);
            $table->string('cost_position')->nullable();
            $table->string('resource_availability')->nullable();
            $table->string('operations_impact')->nullable();
            $table->string('delivery_timeframe')->nullable();
            $table->text('cost_implementation_constraints')->nullable();

            $table->json('significant_risks')->nullable();

            $table->string('overall_finding')->nullable();
            $table->text('finding_basis')->nullable();
            $table->text('next_stage_conditions')->nullable();

            $table->json('supporting_records')->nullable();
            $table->string('other_supporting_record')->nullable();
            $table->string('assessed_by_name_designation')->nullable();
            $table->string('assessed_department')->nullable();
            $table->string('signature_text')->nullable();
            $table->date('confirmation_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_feasibility_assessments', function (Blueprint $table) {
            $table->dropColumn([
                'preliminary_cost',
                'cost_not_available',
                'basis_of_estimate',
                'funding_position',
                'implementation_months',
                'period_not_established',
                'cost_position',
                'resource_availability',
                'operations_impact',
                'delivery_timeframe',
                'cost_implementation_constraints',
                'significant_risks',
                'overall_finding',
                'finding_basis',
                'next_stage_conditions',
                'supporting_records',
                'other_supporting_record',
                'assessed_by_name_designation',
                'assessed_department',
                'signature_text',
                'confirmation_date',
            ]);
        });
    }
};
