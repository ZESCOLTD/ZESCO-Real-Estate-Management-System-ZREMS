<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_register_consolidations', function (Blueprint $table) {
            $table->id();

            $table->string('reference_no')->unique();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('result_asset_register_id')->nullable();

            $table->foreign('created_by', 'fk_ar_cons_created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('result_asset_register_id', 'fk_ar_cons_result_asset')
                ->references('id')
                ->on('asset_registers')
                ->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('asset_register_consolidation_sources', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('asset_register_consolidation_id');
            $table->unsignedBigInteger('asset_register_id');

            $table->foreign('asset_register_consolidation_id', 'fk_ar_cons_src_cons')
                ->references('id')
                ->on('asset_register_consolidations')
                ->cascadeOnDelete();

            $table->foreign('asset_register_id', 'fk_ar_cons_src_asset')
                ->references('id')
                ->on('asset_registers')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['asset_register_consolidation_id', 'asset_register_id'], 'asset_reg_cons_sources_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_register_consolidation_sources');
        Schema::dropIfExists('asset_register_consolidations');
    }
};
