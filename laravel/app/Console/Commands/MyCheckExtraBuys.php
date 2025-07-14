<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\MyDcaHistory;
use App\Models\MyDcaKey;
use App\Models\MyDcaSchedule;
use App\Services\MyExchange;
use App\Services\MyRisk;

class MyCheckExtraBuys extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'my:check-extra-buys {--debug} {--nobuy}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check and execute extra buys for schedules with enable_extra_buys=true';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = (bool) $this->option('debug');
		$nobuy = (bool) $this->option('nobuy');

		$info = 'Checking extra buys - debug: ' . intval($debug) . ', nobuy: ' . intval($nobuy) . ' - ' . date('r') . ' - ' . time();
		$this->info($info);
		Log::channel('my_dca_log')->info($info);

		// Get all active schedules with extra buys enabled
		$schedules = MyDcaSchedule::where('is_active', 1)
			->where('enable_extra_buys', true)
			->get();

		if ($schedules->isEmpty()) {
			$this->comment('No schedules with extra buys enabled found');
			return;
		}

		$exchange = new MyExchange();
		$risk = new MyRisk();

		foreach ($schedules as $schedule) {
			$this->info('Processing extra buys for schedule ID: ' . $schedule->id . ' - ' . $schedule->label);

			// 1. Check risk conditions
			$risk_result = $risk->get(strtoupper($schedule->risk_symbol));
			$this->comment('Current risk: ' . $risk_result['risk']);

			if ($risk_result['risk'] < $schedule->min_risk_buy) {
				$this->info('Risk: ' . $risk_result['risk'] . ' is lower than min risk buy: ' . $schedule->min_risk_buy);
				continue;
			}

			if ($risk_result['risk'] > $schedule->max_risk_buy) {
				$this->info('Risk: ' . $risk_result['risk'] . ' is higher than max risk buy: ' . $schedule->max_risk_buy);
				continue;
			}

			// 2. Check limits
			if ($schedule->extra_buys_executed_count >= $schedule->max_extra_buys_per_interval) {
				$this->info('Extra buys limit reached: ' . $schedule->extra_buys_executed_count . '/' . $schedule->max_extra_buys_per_interval);
				continue;
			}

			// 3. Check time gap
			if ($schedule->last_extra_buy_timestamp > 0) {
				$hoursSinceLastBuy = (time() - $schedule->last_extra_buy_timestamp) / 3600;
				if ($hoursSinceLastBuy < $schedule->min_hours_between_extra_buys) {
					$this->info('Not enough time since last extra buy: ' . round($hoursSinceLastBuy, 1) . ' hours < ' . $schedule->min_hours_between_extra_buys . ' hours');
					continue;
				}
			}

			// 4. Check if DCA key exists
			if (!$schedule->dca_key) {
				$this->info('DCA Key not found');
				continue;
			}

			// 5. Calculate buy amount
			$dca_output = my_get_final_dca_output([
				'risk' => $risk_result['risk'],
				'algorithm' => $schedule->buy_strategy,
				'base_amount' => $schedule->base_amount,
				'debug' => $debug,
			]);

			if ($debug) {
				print_r($schedule->toArray());
				print_r($dca_output);
			}

			// Apply min/max buy amount limits
			if ($schedule->min_buy_amount > 0 && $dca_output['final_amount'] < $schedule->min_buy_amount) {
				$this->comment('Final amount: ' . number_format($dca_output['final_amount']) . ' is lower than min buy amount: ' . number_format($schedule->min_buy_amount) . '. Setting buy to min buy amount.');
				$dca_output['final_amount'] = $schedule->min_buy_amount;
			}

			if ($schedule->max_buy_amount > 0 && $dca_output['final_amount'] > $schedule->max_buy_amount) {
				$this->comment('Final amount: ' . number_format($dca_output['final_amount']) . ' is higher than max buy amount: ' . number_format($schedule->max_buy_amount) . '. Setting buy to max buy amount.');
				$dca_output['final_amount'] = $schedule->max_buy_amount;
			}

			$this->comment($schedule->dca_key->label . ', ' . $schedule->dca_key->exchange_name . ', trying to buy ' . $schedule->pair_name . ' with final amount: ' . number_format($dca_output['final_amount'], 2) . ' (EXTRA BUY)');

			$is_error = 0;
			$buy_result = '';

			// 6. Execute buy
			if (!$nobuy) {
				$this->info('Spot Buy Market - ' . $schedule->dca_key->exchange_name . ' (EXTRA BUY)');

				$buy_result = $exchange->buy_market(
					$schedule->dca_key->exchange_name,
					$schedule->pair_name,
					$dca_output['final_amount'],
					$schedule->dca_key->api_key,
					$schedule->dca_key->api_secret
				);

				if ($buy_result['error']) {
					$this->error('Buy failed: ' . $buy_result['message']);
					$is_error = 1;
					MyDcaKey::where('id', $schedule->key_id)->update(['error_last_check_timestamp' => time()]);
				} else {
					$this->info('Extra buy success');
					MyDcaKey::where('id', $schedule->key_id)->update(['ok_last_check_timestamp' => time()]);
				}
			} else {
				$this->info('Spot Buy Market skipped (EXTRA BUY)');
			}

			if (is_array($buy_result)) {
				$buy_result = base64_encode(json_encode($buy_result));
			}

			// 7. Save to history
			$new_data = [
				'user_id' => $schedule->user_id,
				'schedule_id' => $schedule->id,
				'key_id' => $schedule->dca_key->id,
				'pair_name' => $schedule->pair_name,
				'buy_strategy' => $schedule->buy_strategy,
				'original_amount' => $schedule->base_amount,
				'adjusted_amount' => $dca_output['final_amount'],
				'scheduled_every' => $schedule->scheduled_every,
				'scheduled_every_option' => $schedule->scheduled_every_option,
				'risk_symbol' => $schedule->risk_symbol,
				'min_risk_buy' => $schedule->min_risk_buy,
				'max_risk_buy' => $schedule->max_risk_buy,
				'risk_1' => $risk_result['risk'],
				'dca_note' => $buy_result,
				'is_error' => $is_error,
				'is_extra_buy' => true, // Mark as extra buy
			];
			MyDcaHistory::create($new_data);

			// 8. Update schedule counters
			$schedule->extra_buys_executed_count++;
			$schedule->last_extra_buy_timestamp = time();
			$schedule->save();

			$this->info('Extra buy executed successfully. Count: ' . $schedule->extra_buys_executed_count . '/' . $schedule->max_extra_buys_per_interval);

			if ($schedules->count() > 1) {
				$this->info('Sleeping for 1 seconds before next schedule...');
				sleep(1);
			}
		}

		$this->info('Extra buys check completed');
	}
}
