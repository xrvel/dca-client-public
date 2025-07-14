<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\MyDcaHistory;
use App\Models\MyDcaSchedule;
use Carbon\Carbon;

class MyResetExtraBuysCounters extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'my:reset-extra-buys-counters {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reset extra buys counters based on reset mode and interval';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = (bool) $this->option('debug');

		$info = 'Resetting extra buys counters - debug: ' . intval($debug) . ' - ' . date('r') . ' - ' . time();
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

		$resetCount = 0;

		foreach ($schedules as $schedule) {
			$this->info('Checking reset for schedule ID: ' . $schedule->id . ' - ' . $schedule->label);

			$shouldReset = false;

			if ($schedule->reset_mode == 1) {
				// Relative reset - based on last extra buy in history
				$lastExtraBuy = MyDcaHistory::where('schedule_id', $schedule->id)
					->where('is_extra_buy', true)
					->orderBy('created_at', 'desc')
					->first();

				if ($lastExtraBuy) {
					switch ($schedule->extra_buys_reset_interval) {
						case 'daily':
							$shouldReset = $lastExtraBuy->created_at->diffInHours(now()) >= 24;
							if ($debug) {
								$this->comment('Daily reset check: ' . $lastExtraBuy->created_at->diffInHours(now()) . ' hours since last extra buy');
							}
							break;
						case 'weekly':
							$shouldReset = $lastExtraBuy->created_at->diffInDays(now()) >= 7;
							if ($debug) {
								$this->comment('Weekly reset check: ' . $lastExtraBuy->created_at->diffInDays(now()) . ' days since last extra buy');
							}
							break;
						case 'monthly':
							$shouldReset = $lastExtraBuy->created_at->diffInDays(now()) >= 30;
							if ($debug) {
								$this->comment('Monthly reset check: ' . $lastExtraBuy->created_at->diffInDays(now()) . ' days since last extra buy');
							}
							break;
					}
				} else {
					// No extra buys in history, reset if counter > 0
					if ($schedule->extra_buys_executed_count > 0) {
						$shouldReset = true;
						if ($debug) {
							$this->comment('No extra buys in history, resetting counter from ' . $schedule->extra_buys_executed_count);
						}
					}
				}
			} else if ($schedule->reset_mode == 2) {
				// Absolute reset - calendar boundaries with tolerance
				$toleranceMinutes = 15; // Allow 15 minutes tolerance for cron delays
				
				switch ($schedule->extra_buys_reset_interval) {
					case 'daily':
						// Reset if it's 00:00-00:15 (with tolerance)
						$shouldReset = now()->hour === 0 && now()->minute <= $toleranceMinutes;
						if ($debug) {
							$this->comment('Daily absolute reset check: ' . now()->format('H:i') . ' (tolerance: 00:00-00:' . $toleranceMinutes . ')');
						}
						break;
					case 'weekly':
						// Reset if it's Monday 00:00-00:15 (with tolerance)
						$shouldReset = now()->isMonday() && now()->hour === 0 && now()->minute <= $toleranceMinutes;
						if ($debug) {
							$this->comment('Weekly absolute reset check: ' . now()->format('l H:i') . ' (Monday: ' . (now()->isMonday() ? 'Yes' : 'No') . ', tolerance: 00:00-00:' . $toleranceMinutes . ')');
						}
						break;
					case 'monthly':
						// Reset if it's 1st of month 00:00-00:15 (with tolerance)
						$shouldReset = now()->day === 1 && now()->hour === 0 && now()->minute <= $toleranceMinutes;
						if ($debug) {
							$this->comment('Monthly absolute reset check: Day ' . now()->day . ' at ' . now()->format('H:i') . ' (tolerance: 00:00-00:' . $toleranceMinutes . ')');
						}
						break;
				}
			}

			if ($shouldReset) {
				$oldCount = $schedule->extra_buys_executed_count;
				$schedule->extra_buys_executed_count = 0;
				$schedule->save();

				$resetCount++;
				$this->info('Reset counter for schedule ' . $schedule->id . ': ' . $oldCount . ' -> 0');

				Log::channel('my_dca_log')->info('Reset extra buys counter for schedule ' . $schedule->id . ': ' . $oldCount . ' -> 0');
			} else {
				if ($debug) {
					$this->comment('No reset needed for schedule ' . $schedule->id . ' (count: ' . $schedule->extra_buys_executed_count . ')');
				}
			}
		}

		$this->info('Reset process completed. Reset ' . $resetCount . ' schedules');
		Log::channel('my_dca_log')->info('Reset process completed. Reset ' . $resetCount . ' schedules');
	}
}
