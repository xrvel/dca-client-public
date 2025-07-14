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
        Schema::table('my_dca_keys', function (Blueprint $table) {
            $table->boolean('uses_ccxt')->default(false)->after('exchange_name')->comment('Whether this exchange uses CCXT library');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('my_dca_keys', function (Blueprint $table) {
            $table->dropColumn('uses_ccxt');
        });
    }
}; 