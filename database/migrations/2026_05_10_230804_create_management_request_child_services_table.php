<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('management_request_child_services', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('request_parent_service_id');

            $table->foreign(
                'request_parent_service_id',
                'mr_child_parent_fk'
            )
                ->references('id')
                ->on('management_request_parent_services')
                ->onDelete('cascade');

            $table->foreignId('child_service_id')
                ->constrained('management_request_child_services')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('management_request_child_services');
    }
};
