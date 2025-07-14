<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\MyDcaHistory;
use App\Models\MyDcaKey;
use App\Models\MyDcaSchedule;
use App\Services\MyExchange;
use App\Services\MyRisk;

class MyRunDca extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'my:run-dca {--schevery=} {--scheveryoption=} {--id=} {--debug} {--nobuy}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run DCA with certain schedule {--schevery=} {--scheveryoption=} {--id=} {--debug} {--nobuy}';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$schedule_every = $this->option('schevery');
		$schedule_every_option = $this->option('scheveryoption');
		$schedule_id = (int) $this->option('id');
		$debug = (bool) $this->option('debug');
		$nobuy = (bool) $this->option('nobuy');

		if (is_string($schedule_every)) {
			$schedule_every = trim($schedule_every);
		}

		if (is_string($schedule_every_option)) {
			$schedule_every_option = trim($schedule_every_option);
		}

		$info = 'schevery: ' . $schedule_every . ', scheveryoption: ' . $schedule_every_option . ', Schedule id : ' . $schedule_id . ', debug: ' . intval($debug) . ', nobuy: ' . intval($nobuy) . ' - ' . date('r') . ' - ' . time();
		$this->info($info);

		if ('' != $schedule_every && !in_array($schedule_every, ['everyMinute', 'everyFiveMinutes', 'everyTenMinutes', 'everyFifteenMinutes', 'everyThirtyMinutes'])) {
			Log::channel('my_dca_log')->info($info);
		}

		if (empty($schedule_every) && 0 == $schedule_id) {
			$this->error('Schedule every is required : --schevery=<option>, or else force certain ID with --id=<schedule_id>');
			return;
		}

		if (0 != $schedule_id) {
			$schedules = MyDcaSchedule::where('is_active', 1)
				->where('scheduled_every', 'custom')
				->where('id', $schedule_id)
				->get();
		} else if (!empty($schedule_every_option)) {
			$schedules = MyDcaSchedule::where('is_active', 1)
				->where('scheduled_every', $schedule_every)
				->where('scheduled_every_option', $schedule_every_option)
				->get();
		} else {
			$schedules = MyDcaSchedule::where('is_active', 1)
				->where('scheduled_every', $schedule_every)
				->get();
		}

		if ($schedules->isEmpty()) {
			$this->comment('No schedule found, check if schedule is active');
			//Log::channel('my_dca_log')->info('No schedule found, check if schedule is active');
			return;
		}

		$exchange = new MyExchange();
		$risk = new MyRisk();

		foreach ($schedules as $schedule) {
			$info = 'Processing schedule ids: ' . $schedule->id;
			$this->info($info);
			//Log::channel('my_dca_log')->info($info);

			$risk_result = $risk->get(strtoupper($schedule->risk_symbol));

			$this->comment('Risk: ' . $risk_result['risk']);

			if ($risk_result['risk'] < $schedule->min_risk_buy) {
				$this->info('Risk: ' . $risk_result['risk'] . ' is lower than min risk buy: ' . $schedule->min_risk_buy);
				continue;
			}

			if ($risk_result['risk'] > $schedule->max_risk_buy) {
				$this->info('Risk: ' . $risk_result['risk'] . ' is higher than max risk buy: ' . $schedule->max_risk_buy);
				continue;
			}

			if (!$schedule->dca_key) {
				$this->info('DCA Key not found');
				continue;
			}

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

			if ($schedule->min_buy_amount > 0 && $dca_output['final_amount'] < $schedule->min_buy_amount) {
				$this->comment('Final amount: ' . number_format($dca_output['final_amount']) . ' is lower than min buy amount: ' . number_format($schedule->min_buy_amount) . '. Setting buy to min buy amount.');
				$dca_output['final_amount'] = $schedule->min_buy_amount;
			}

			if ($schedule->max_buy_amount > 0 && $dca_output['final_amount'] > $schedule->max_buy_amount) {
				$this->comment('Final amount: ' . number_format($dca_output['final_amount']) . ' is higher than max buy amount: ' . number_format($schedule->max_buy_amount) . '. Setting buy to max buy amount.');
				$dca_output['final_amount'] = $schedule->max_buy_amount;
			}

			$this->comment($schedule->dca_key->label . ', ' . $schedule->dca_key->exchange_name . ', trying to buy ' . $schedule->pair_name . ' with final amount: ' . number_format($dca_output['final_amount'], 2));

			$is_error = 0;

			$buy_result = '';

			if (!$nobuy) {
				$this->info('Spot Buy Market - ' . $schedule->dca_key->exchange_name);

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
					$this->info('Buy success');
					MyDcaKey::where('id', $schedule->key_id)->update(['ok_last_check_timestamp' => time()]);
				}
			} else {
				$this->info('Spot Buy Market skipped');
			}

			if (is_array($buy_result)) {
				$buy_result = base64_encode(json_encode($buy_result));
			}

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
				'is_error' => $is_error
			];
			MyDcaHistory::create($new_data);

			if ($schedules->count() > 1) {
				$this->info('Sleeping for 1 seconds before next schedule...');
				sleep(1);
			}
		}

		$this->info('Run DCA End');
	}
}
