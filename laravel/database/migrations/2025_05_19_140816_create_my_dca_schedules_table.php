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
		if (!Schema::hasTable('my_dca_schedules')) {
			Schema::create('my_dca_schedules', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('user_id')->default(0)->index()->comment('User ID of the owner of this schedule.');
				$table->string('label', 100)->default('')->comment('Label for the schedules');
				$table->string('pair_name', 100)->default('')->comment('E.g BTCUSDT');
				$table->unsignedBigInteger('key_id')->default(0)->index()->comment('Key ID');
				$table->string('buy_strategy', 100)->default('');
				$table->unsignedBigInteger('base_amount')->default(0);
				$table->string('scheduled_every', 100)->default('')->index()->comment('Scheduled every');
				$table->string('scheduled_every_option', 100)->default('')->index()->comment('Scheduled every additional option');
				$table->unsignedBigInteger('risk_source')->default(0);
				$table->string('risk_symbol', 100)->default('')->comment('E.g BTC');
				$table->float('min_risk_buy', 10, 2)->unsigned()->default(0)->comment('Min risk buy amount. If risk is less than this, then skip buying.');
				$table->float('max_risk_buy', 10, 2)->unsigned()->default(100)->comment('Max risk buy amount. If risk is more than this, then skip buying.');
				$table->unsignedBigInteger('min_buy_amount')->default(0)->comment('Min buy amount in fiat.');
				$table->unsignedBigInteger('max_buy_amount')->default(0)->comment('Max buy amount in fiat.');
				$table->text('description')->comment('Description of the schedule.');
				$table->unsignedBigInteger('schedule_last_run')->default(0)->index()->comment('Last time the schedule was run.');
				$table->unsignedTinyInteger('is_active')->default(0)->index()->comment('Is the schedule active? 0 = No, 1 = Yes');
				$table->timestamps();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('my_dca_schedules');
	}
};
