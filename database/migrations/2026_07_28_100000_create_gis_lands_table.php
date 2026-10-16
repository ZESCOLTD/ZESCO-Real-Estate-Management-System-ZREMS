<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gis_lands', function (Blueprint $table) {
            $table->id();
            $table->string('plot_number', 100)->unique();
            $table->string('property_name');
            $table->string('province', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('chiefdom', 100)->nullable();
            $table->decimal('area', 15, 6)->nullable()->comment('Area in hectares');
            $table->enum('status', ['acquired', 'negotiations', 'disputed', 'survey_pending', 'archived'])
                  ->default('survey_pending');
            $table->string('purpose')->nullable();
            $table->string('owner')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('survey_reference', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('province');
            $table->index('district');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gis_lands');
    }
};
