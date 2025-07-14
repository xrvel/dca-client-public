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
		Schema::create('my_dca_history', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->default(0)->index()->comment('User ID of the owner of this schedule.');
			$table->unsignedBigInteger('schedule_id')->default(0)->index()->comment('Schedule ID');
			$table->unsignedBigInteger('key_id')->default(0)->index()->comment('Key ID.');
			$table->string('pair_name', 100)->default('')->comment('E.g BTCUSDT');
			$table->string('buy_strategy', 100)->default('');
			$table->unsignedBigInteger('original_amount')->default(0);
			$table->unsignedBigInteger('adjusted_amount')->default(0);
			$table->string('scheduled_every', 100)->default('')->index()->comment('Scheduled every');
			$table->string('scheduled_every_option', 100)->default('')->index()->comment('Scheduled every additional option');
			$table->unsignedBigInteger('risk_source')->default(0);
			$table->string('risk_symbol', 100)->default('')->comment('E.g BTC');
			$table->float('min_risk_buy', 10, 2)->unsigned()->default(0)->comment('Min risk buy amount. If risk is less than this, then skip buying.');
			$table->float('max_risk_buy', 10, 2)->unsigned()->default(0)->comment('Max risk buy amount. If risk is more than this, then skip buying.');
			$table->float('risk_1', 10, 2)->unsigned()->default(0);
			$table->float('risk_2', 10, 2)->unsigned()->default(0);
			$table->float('risk_3', 10, 2)->unsigned()->default(0);
			$table->text('dca_note')->comment('Debug information.');
			$table->unsignedTinyInteger('is_error')->default(0)->index();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('my_dca_history');
	}
};
