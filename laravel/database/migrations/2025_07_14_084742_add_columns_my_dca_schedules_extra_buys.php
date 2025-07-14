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
        if (Schema::hasTable('my_dca_schedules')) {
            Schema::table('my_dca_schedules', function (Blueprint $table) {
                if (!Schema::hasColumn('my_dca_schedules', 'enable_extra_buys')) {
                    $table->boolean('enable_extra_buys')->default(false)->comment('Enable extra buys for this schedule');
                }
                
                if (!Schema::hasColumn('my_dca_schedules', 'extra_buys_reset_interval')) {
                    $table->enum('extra_buys_reset_interval', ['daily', 'weekly', 'monthly'])->default('daily')->comment('Interval for resetting extra buys counter');
                }
                
                if (!Schema::hasColumn('my_dca_schedules', 'extra_buys_executed_count')) {
                    $table->unsignedInteger('extra_buys_executed_count')->default(0)->comment('Number of extra buys executed in current interval');
                }
                
                if (!Schema::hasColumn('my_dca_schedules', 'max_extra_buys_per_interval')) {
                    $table->unsignedInteger('max_extra_buys_per_interval')->default(0)->comment('Maximum number of extra buys allowed per interval');
                }
                
                if (!Schema::hasColumn('my_dca_schedules', 'min_hours_between_extra_buys')) {
                    $table->unsignedInteger('min_hours_between_extra_buys')->default(0)->comment('Minimum hours between extra buys');
                }
                
                if (!Schema::hasColumn('my_dca_schedules', 'reset_mode')) {
                    $table->unsignedTinyInteger('reset_mode')->default(0)->comment('1=relative to last buy, 2=absolute calendar boundary');
                }
                
                if (!Schema::hasColumn('my_dca_schedules', 'last_extra_buy_timestamp')) {
                    $table->unsignedBigInteger('last_extra_buy_timestamp')->default(0)->comment('Unix timestamp of last extra buy');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('my_dca_schedules')) {
            Schema::table('my_dca_schedules', function (Blueprint $table) {
                $table->dropColumn([
                    'enable_extra_buys',
                    'extra_buys_reset_interval',
                    'extra_buys_executed_count',
                    'max_extra_buys_per_interval',
                    'min_hours_between_extra_buys',
                    'reset_mode',
                    'last_extra_buy_timestamp'
                ]);
            });
        }
    }
};
