<?php

namespace App\Http\Controllers;

use App\Models\MyDcaKey;
use App\Models\MyDcaSchedule;
use App\Services\MyRisk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyDcaScheduleController extends Controller
{
	public function index()
	{
		$dcaschedules = MyDcaSchedule::where('user_id', Auth::user()->id)
			->orderBy('is_active', 'desc')
			->orderBy('max_risk_buy', 'desc')
			->orderBy('min_risk_buy', 'desc')
			->orderBy('pair_name', 'asc')
			->orderBy('base_amount', 'desc')
			->orderBy('label', 'asc')
			->orderBy('id', 'asc')
			->paginate(10);

		$risk_symbols = [];

		$risk_service = new MyRisk();

		foreach ($dcaschedules as $schedule) {
			if (!isset($risk_symbol[$schedule->risk_symbol])) {
				$_temp_risk = $risk_service->get($schedule->risk_symbol);
				$risk_symbols[$schedule->risk_symbol] = $_temp_risk['risk'] ?? 0;
			}
		}

		unset($risk_service);

		return view('inner.dcaschedules.index', compact('dcaschedules', 'risk_symbols'));
	}

	public function create()
	{
		$dca_keys = MyDcaKey::where('user_id', Auth::user()->id)
			->orderBy('label', 'asc')
			->orderBy('exchange_name', 'asc')
			->orderBy('id', 'asc')
			->get();

		// Get exchange service for pair normalization
		$exchange_service = new \App\Services\MyExchange();
		$exchanges = $exchange_service->get_exchanges();

		return view('inner.dcaschedules.create', compact('dca_keys', 'exchanges'));
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'label' => 'required|string|max:100',
			'pair_name' => 'required|string|max:100',
			'key_id' => 'required|integer|min:1',
			'buy_strategy' => 'required|string|max:100',
			'base_amount' => 'required|integer|min:1',
			'scheduled_every' => 'required|string|max:100',
			'scheduled_every_option' => 'nullable|string|max:100',
			'risk_symbol' => 'required|string|max:100',
			'min_risk_buy' => 'required|numeric|min:0|max:100',
			'max_risk_buy' => 'required|numeric|min:0|max:100',
			'min_buy_amount' => 'required|integer',
			'max_buy_amount' => 'required|integer',
			'description' => 'nullable|string',
			'is_active' => 'required|boolean',
			'enable_extra_buys' => 'boolean',
			'extra_buys_reset_interval' => 'nullable|in:daily,weekly,monthly',
			'extra_buys_executed_count' => 'nullable|integer|min:0',
			'max_extra_buys_per_interval' => 'nullable|integer|min:0|max:100',
			'min_hours_between_extra_buys' => 'nullable|integer|min:1|max:168',
			'reset_mode' => 'nullable|integer|in:0,1,2',
			'last_extra_buy_timestamp' => 'nullable|integer|min:0',
		]);

		// Normalize the pair name before saving
		$dca_key = MyDcaKey::find($validated['key_id']);
		if ($dca_key) {
			// Skip normalization for binanceproxy - keep original format
			if ($dca_key->exchange_name === 'binance_proxy') {
				// Do nothing - keep the pair as entered
			} else {
				$ccxt = new \App\Services\MyCcxtExchange();
				$normalized_pair = $ccxt->normalize_pair($dca_key->exchange_name, $validated['pair_name']);

				if ($normalized_pair) {
					$validated['pair_name'] = $normalized_pair;
				} else {
					return redirect()->back()
						->withInput()
						->withErrors(['pair_name' => 'Invalid trading pair for this exchange. Please check the available pairs.']);
				}
			}
		}

		$validated['user_id'] = Auth::user()->id;
		$validated['scheduled_every_option'] = trim($validated['scheduled_every_option']);
		$validated['description'] = trim($validated['description']);

		// Set default values for extra buys fields
		$validated['enable_extra_buys'] = $validated['enable_extra_buys'] ?? false;
		$validated['extra_buys_reset_interval'] = $validated['extra_buys_reset_interval'] ?? 'daily';
		$validated['extra_buys_executed_count'] = $validated['extra_buys_executed_count'] ?? 0;
		$validated['max_extra_buys_per_interval'] = $validated['max_extra_buys_per_interval'] ?? 0;
		$validated['min_hours_between_extra_buys'] = $validated['min_hours_between_extra_buys'] ?? 1;
		$validated['reset_mode'] = $validated['reset_mode'] ?? 1;
		$validated['last_extra_buy_timestamp'] = $validated['last_extra_buy_timestamp'] ?? 0;

		$dcaschedule = MyDcaSchedule::create($validated);

		return redirect()->route('dcaschedules.index', $dcaschedule->id)
			->with('success', 'DCA Schedule created successfully. ID: ' . $dcaschedule->id);
	}

	public function edit(MyDcaSchedule $dcaschedule)
	{
		if ($dcaschedule->user_id != Auth::user()->id) {
			return redirect()->route('dcaschedules.index')
				->with('error', 'You do not have permission to edit this DCA Schedule.');
		}

		$dca_keys = MyDcaKey::where('user_id', Auth::user()->id)
			->orderBy('label', 'asc')
			->orderBy('exchange_name', 'asc')
			->orderBy('id', 'asc')
			->get();

		// Get exchange service for pair normalization
		$exchange_service = new \App\Services\MyExchange();
		$exchanges = $exchange_service->get_exchanges();

		return view('inner.dcaschedules.edit', compact('dcaschedule', 'dca_keys', 'exchanges'));
	}

	public function update(Request $request, MyDcaSchedule $dcaschedule)
	{
		if ($dcaschedule->user_id != Auth::user()->id) {
			return redirect()->route('dcaschedules.index')
				->with('error', 'You do not have permission to edit this DCA Schedule.');
		}

		$validated = $request->validate([
			'label' => 'required|string|max:100',
			'pair_name' => 'required|string|max:100',
			'key_id' => 'required|integer|min:1',
			'buy_strategy' => 'required|string|max:100',
			'base_amount' => 'required|integer|min:1',
			'scheduled_every' => 'required|string|max:100',
			'scheduled_every_option' => 'nullable|string|max:100',
			'risk_symbol' => 'required|string|max:100',
			'min_risk_buy' => 'required|numeric|min:0|max:100',
			'max_risk_buy' => 'required|numeric|min:0|max:100',
			'min_buy_amount' => 'required|integer',
			'max_buy_amount' => 'required|integer',
			'description' => 'nullable|string',
			'is_active' => 'required|boolean',
			'enable_extra_buys' => 'boolean',
			'extra_buys_reset_interval' => 'nullable|in:daily,weekly,monthly',
			'extra_buys_executed_count' => 'nullable|integer|min:0',
			'max_extra_buys_per_interval' => 'nullable|integer|min:0|max:100',
			'min_hours_between_extra_buys' => 'nullable|integer|min:1|max:168',
			'reset_mode' => 'nullable|integer|in:0,1,2',
			'last_extra_buy_timestamp' => 'nullable|integer|min:0',
		]);

		// Normalize the pair name before saving
		$dca_key = MyDcaKey::find($validated['key_id']);
		if ($dca_key) {
			// Skip normalization for binanceproxy - keep original format
			if ($dca_key->exchange_name === 'binance_proxy') {
				// Do nothing - keep the pair as entered
			} else {
				$ccxt = new \App\Services\MyCcxtExchange();
				$normalized_pair = $ccxt->normalize_pair($dca_key->exchange_name, $validated['pair_name']);

				if ($normalized_pair) {
					$validated['pair_name'] = $normalized_pair;
				} else {
					return redirect()->back()
						->withInput()
						->withErrors(['pair_name' => 'Invalid trading pair for this exchange. Please check the available pairs.']);
				}
			}
		}

		$validated['scheduled_every_option'] = trim($validated['scheduled_every_option']);
		$validated['description'] = trim($validated['description']);

		// Set default values for extra buys fields if not provided
		$validated['enable_extra_buys'] = $validated['enable_extra_buys'] ?? false;
		$validated['extra_buys_reset_interval'] = $validated['extra_buys_reset_interval'] ?? 'daily';
		$validated['max_extra_buys_per_interval'] = $validated['max_extra_buys_per_interval'] ?? 0;
		$validated['min_hours_between_extra_buys'] = $validated['min_hours_between_extra_buys'] ?? 1;
		$validated['reset_mode'] = $validated['reset_mode'] ?? 1;

		$dcaschedule->update($validated);

		return redirect()->route('dcaschedules.index', $dcaschedule->id)
			->with('success', 'DCA Schedule updated successfully.');
	}

	public function destroy(MyDcaSchedule $dcaschedule)
	{
		if ($dcaschedule->user_id != Auth::user()->id) {
			return redirect()->route('dcaschedules.index')
				->with('error', 'You do not have permission to edit this DCA Schedule.');
		}

		$dcaschedule->delete();

		return redirect()->route('dcaschedules.index')
			->with('success', 'DCA Schedule deleted successfully.');
	}
}
