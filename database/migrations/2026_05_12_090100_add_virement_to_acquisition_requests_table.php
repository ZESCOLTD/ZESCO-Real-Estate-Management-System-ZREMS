<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acquisition_requests', function (Blueprint $table) {
            if (Schema::hasColumn('acquisition_requests', 'virement_file_path')) {
                return;
            }

            $table->string('virement_file_path')->nullable()->after('budget_line');
            $table->foreignId('virement_uploaded_by')->nullable()->after('virement_file_path')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('virement_uploaded_at')->nullable()->after('virement_uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::table('acquisition_requests', function (Blueprint $table) {
            if (Schema::hasColumn('acquisition_requests', 'virement_uploaded_by')) {
                $table->dropConstrainedForeignId('virement_uploaded_by');
            }

            if (Schema::hasColumn('acquisition_requests', 'virement_uploaded_at')) {
                $table->dropColumn('virement_uploaded_at');
            }

            if (Schema::hasColumn('acquisition_requests', 'virement_file_path')) {
                $table->dropColumn('virement_file_path');
            }
        });
    }
};
