<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_childrens')) {
            return;
        }

        Schema::table('service_childrens', function (Blueprint $table) {
            if (! Schema::hasColumn('service_childrens', 'model')) {
                $table->string('model')->nullable()->index();
            }

            if (! Schema::hasColumn('service_childrens', 'type')) {
                // Use string for broad DB compatibility (Oracle/MySQL)
                $table->string('type')->default('new_acquisition')->index();
            }

            if (! Schema::hasColumn('service_childrens', 'service_code')) {
                $table->string('service_code')->nullable()->index();
            }

            if (! Schema::hasColumn('service_childrens', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }

            if (! Schema::hasColumn('service_childrens', 'created_at')) {
                $table->timestamps();
            }

            if (! Schema::hasColumn('service_childrens', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_childrens')) {
            return;
        }

        Schema::table('service_childrens', function (Blueprint $table) {
            foreach (['model', 'type', 'service_code', 'is_active', 'deleted_at'] as $col) {
                if (Schema::hasColumn('service_childrens', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
