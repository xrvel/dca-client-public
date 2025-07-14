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
        if (Schema::hasTable('my_dca_history')) {
            Schema::table('my_dca_history', function (Blueprint $table) {
                if (!Schema::hasColumn('my_dca_history', 'is_extra_buy')) {
                    $table->boolean('is_extra_buy')->default(false)->comment('Whether this history record was an extra buy');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('my_dca_history')) {
            Schema::table('my_dca_history', function (Blueprint $table) {
                $table->dropColumn('is_extra_buy');
            });
        }
    }
};
