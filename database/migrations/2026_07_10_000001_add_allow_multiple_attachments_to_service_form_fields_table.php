<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_form_fields') || Schema::hasColumn('service_form_fields', 'allow_multiple_attachments')) {
            return;
        }

        Schema::table('service_form_fields', function (Blueprint $table) {
            $table->boolean('allow_multiple_attachments')->default(false)->after('allow_attachment');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_form_fields') || !Schema::hasColumn('service_form_fields', 'allow_multiple_attachments')) {
            return;
        }

        Schema::table('service_form_fields', function (Blueprint $table) {
            $table->dropColumn('allow_multiple_attachments');
        });
    }
};
