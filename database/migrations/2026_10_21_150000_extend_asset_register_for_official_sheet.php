<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_registers', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_registers', 'business_unit')) {
                $table->string('business_unit')->nullable()->index();
            }
            if (! Schema::hasColumn('asset_registers', 'cost_centre')) {
                $table->string('cost_centre')->nullable()->index();
            }
            if (! Schema::hasColumn('asset_registers', 'area')) {
                $table->string('area')->nullable()->index();
            }
            if (! Schema::hasColumn('asset_registers', 'sub_location')) {
                $table->string('sub_location')->nullable()->index();
            }
            if (! Schema::hasColumn('asset_registers', 'life_in_months')) {
                $table->unsignedInteger('life_in_months')->nullable();
            }
            if (! Schema::hasColumn('asset_registers', 'status')) {
                $table->string('status')->nullable()->index();
            }
            if (! Schema::hasColumn('asset_registers', 'reportline')) {
                $table->string('reportline')->nullable()->index();
            }
            if (! Schema::hasColumn('asset_registers', 'source_row')) {
                $table->json('source_row')->nullable();
            }
            if (! Schema::hasColumn('asset_registers', 'last_import_job_id')) {
                $table->foreignId('last_import_job_id')->nullable()->constrained('import_jobs')->nullOnDelete();
            }
            if (! Schema::hasColumn('asset_registers', 'imported_at')) {
                $table->timestamp('imported_at')->nullable();
            }
        });

        if (! Schema::hasTable('asset_register_configurations')) {
            Schema::create('asset_register_configurations', function (Blueprint $table) {
                $table->id();
                $table->string('field_key', 80);
                $table->string('value', 255);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['field_key', 'value'], 'asset_reg_config_value_unique');
            });
        }

        Schema::whenTableDoesntHaveIndex(
                'asset_register_configurations',
            'asset_reg_config_lookup_idx',
            fn (Blueprint $table) => $table->index(['field_key', 'is_active', 'sort_order'], 'asset_reg_config_lookup_idx')
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_register_configurations');

        Schema::table('asset_registers', function (Blueprint $table) {
            if (Schema::hasColumn('asset_registers', 'last_import_job_id')) {
                $table->dropForeign(['last_import_job_id']);
            }

            $columns = array_values(array_filter([
                'business_unit',
                'cost_centre',
                'area',
                'sub_location',
                'life_in_months',
                'status',
                'reportline',
                'source_row',
                'last_import_job_id',
                'imported_at',
            ], fn (string $column) => Schema::hasColumn('asset_registers', $column)));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
