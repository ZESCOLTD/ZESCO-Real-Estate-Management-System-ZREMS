<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_registers', function (Blueprint $table) {
            $table->id();

            $table->string('book_type_code')->nullable()->index();

            $table->string('asset_number')->unique();
            $table->string('asset_id')->nullable()->index();
            $table->string('tag_number')->nullable()->index();

            $table->date('date_placed_in_service')->nullable()->index();

            $table->string('asset_category')->nullable()->index();
            $table->string('major_category')->nullable()->index();
            $table->string('sub_major_category')->nullable();
            $table->string('minor_category')->nullable();
            $table->string('sub_minor_category')->nullable();

            $table->text('description')->nullable();

            $table->decimal('asset_cost', 18, 2)->nullable();
            $table->decimal('original_asset_cost', 18, 2)->nullable();
            $table->string('asset_location')->nullable()->index();
            $table->decimal('net_book_value', 18, 2)->nullable();
            $table->decimal('deprn_reserve', 18, 2)->nullable();
            $table->decimal('ytd_deprn', 18, 2)->nullable();

            $table->string('asset_cost_acct')->nullable();
            $table->string('asset_clearing_account')->nullable();
            $table->string('reserve_account')->nullable();
            $table->string('deprn_expense_account')->nullable();

            $table->string('town')->nullable()->index();
            $table->string('location')->nullable()->index();
            $table->string('dept')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_registers');
    }
};
