{{-- resources/views/mydcaschedules/create.blade.php --}}
@extends('layouts.app')

@section('html_title')
	Create DCA Schedules
@endsection

@section('content')
	<div class="container mt-4">
		<h1 class="mb-4">Create New DCA Schedule</h1>

		@if ($errors->any())
			<div class="alert alert-danger">
				<ul>
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form action="{{ route('dcaschedules.store') }}" method="POST">
			@csrf

			<div class="mb-3">
				<label for="label" class="form-label">Label *</label>
				<input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label"
					value="{{ old('label') }}" required autofocus placeholder="Enter label for the schedule">
				@error('label')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="pair_name" class="form-label">Pair Name *</label>
				<div class="form-text">
					Enter any format (e.g., BTCUSDT, BTC/USDT, btcusdt). The system will automatically normalize it for the selected
					exchange.
				</div>
				<input type="text" class="form-control @error('pair_name') is-invalid @enderror" id="pair_name" name="pair_name"
					value="{{ old('pair_name') }}" required placeholder="e.g., BTCUSDT">
				@error('pair_name')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="key_id" class="form-label">Exchange Key *</label>
				@if ($dca_keys->isEmpty())
					<div class="alert alert-warning" role="alert">
						No exchange keys available. Please create an exchange key first.
						<a href="{{ route('dcakeys.create') }}" class="btn btn-link">Create Exchange Key</a>
					</div>
				@else
					<select class="form-select @error('key_id') is-invalid @enderror" id="key_id" name="key_id" required>
						<option value="">Select Exchange Key</option>
						@foreach ($dca_keys as $item)
							<option value="{{ $item->id }}" @selected(old('key_id') == $item->id)>{{ $item->label }} - {{ $item->exchange_name }}
								- {{ $item->id }}</option>
						@endforeach
					</select>
				@endif
				@error('key_id')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="buy_strategy" class="form-label">Buy Strategy *</label>
				<div class="form-text">
					Go to <a href="{{ route('risk.simulation') }}" target="_blank">Risk simulation</a> to see the effect.
				</div>
				<select class="form-select @error('buy_strategy') is-invalid @enderror" id="buy_strategy" name="buy_strategy"
					required>
					<option value="fixed">Fixed</option>
					<option value="linear">Dynamic Linear</option>
					<option value="log_1" @selected(old('buy_strategy') == 'log_1')>Dynamic Logarithm 1</option>
					<option value="log_2" @selected(old('buy_strategy') == 'log_2')>Dynamic Logarithm 2 (aggresive)</option>
					<option value="log_low_1" @selected(old('buy_strategy') == 'log_low_1')>Dynamic Logarithm - Low Threshold 1</option>
					<option value="square_1" @selected(old('buy_strategy') == 'square_1')>Squared 1 (aggresive)</option>
				</select>
				@error('buy_strategy')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="base_amount" class="form-label">Base Amount *</label>
				<div class="form-text">
					Base amount of DCA in fiat which will be used for each buy.
					Final amount may varies based on algorithm &amp; risk metric result. Go to <a href="{{ route('risk.simulation') }}"
						target="_blank">Risk simulation</a> to see the effect.
				</div>
				<input type="number" class="form-control @error('base_amount') is-invalid @enderror" id="base_amount"
					name="base_amount" value="{{ old('base_amount') }}" required placeholder="Enter base amount">
				@error('base_amount')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3" id="scheduled_every_group">
				<label for="scheduled_every" class="form-label">Scheduled Every *</label>
				<select class="form-select @error('scheduled_every') is-invalid @enderror" id="scheduled_every"
					name="scheduled_every" required>
					<option value="everyMinute" @selected(old('scheduled_every') == 'everyMinute')>Every 1 Minute</option>
					<option value="everyFiveMinutes" @selected(old('scheduled_every') == 'everyFiveMinutes')>Every 5 Minutes</option>
					<option value="everyTenMinutes" @selected(old('scheduled_every') == 'everyTenMinutes')>Every 10 Minutes</option>
					<option value="everyFifteenMinutes" @selected(old('scheduled_every') == 'everyFifteenMinutes')>Every 15 Minutes</option>
					<option value="everyThirtyMinutes" @selected(old('scheduled_every') == 'everyThirtyMinutes')>Every 30 Minutes</option>
					<option value="hourly" @selected(old('scheduled_every') == 'hourly')>Every 1 Hour</option>
					<option value="everyOddHour" @selected(old('scheduled_every') == 'everyOddHour')>Every Odd Hours</option>
					<option value="everyTwoHours" @selected(old('scheduled_every') == 'everyTwoHours')>Every 2 Hours</option>
					<option value="everyThreeHours" @selected(old('scheduled_every') == 'everyThreeHours')>Every 3 Hours</option>
					<option value="everyFourHours" @selected(old('scheduled_every') == 'everyFourHours')>Every 4 Hours</option>
					<option value="everySixHours" @selected(old('scheduled_every') == 'everySixHours')>Every 6 Hours</option>
					<option value="daily" @selected(old('scheduled_every') == 'daily')>Daily</option>
					<option value="weekly" @selected(old('scheduled_every') == 'weekly')>Weekly</option>
					<option value="monthly" @selected(old('scheduled_every') == 'monthly')>Monthly</option>
					<option value="custom" @selected(old('scheduled_every') == 'custom')>Custom</option>
				</select>
				@error('scheduled_every')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3" id="scheduled_every_option_group">
				<label for="scheduled_every_option" class="form-label">Scheduled Every Option</label>
				<div class="form-text">
					You can leave this field empty. Optional minutes (fill basic numeric like &quot;15&quot;) or hours (fill hours like
					&quot;13:00&quot;).,<br />
					Current time is <?php echo date('r'); ?>. To change timezone, edit <code>config/app.php</code> file in the
					<code>timezone</code> section.
				</div>
				<input type="text" class="form-control @error('scheduled_every_option') is-invalid @enderror"
					id="scheduled_every_option" name="scheduled_every_option" value="{{ old('scheduled_every_option') }}"
					placeholder="e.g., 15, 30, 60 or 13:00">
				@error('scheduled_every_option')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="risk_symbol" class="form-label">Risk Symbol *</label>
				<div class="form-text">
					Risk metric will be based on this symbol.
				</div>
				<select class="form-select @error('risk_symbol') is-invalid @enderror" id="risk_symbol" name="risk_symbol"
					required>
					<option value="BTC">BTC</option>
					<option value="ETH" @selected(old('risk_symbol') == 'ETH')>ETH</option>
					<option value="BNB" @selected(old('risk_symbol') == 'BNB')>BNB</option>
					<option value="SOL" @selected(old('risk_symbol') == 'SOL')>Solana</option>
					<option value="ADA" @selected(old('risk_symbol') == 'ADA')>Cardano</option>
					<option value="XRP" @selected(old('risk_symbol') == 'XRP')>XRP</option>
				</select>
				@error('risk_symbol')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="min_risk_buy" class="form-label">Min Risk Buy *</label>
				<div class="form-text">
					If risk metric is more or equal to this value, then DCA will be executed. Risk metric is ranging from 0.00 to
					100.00.
				</div>
				<input type="number" step="0.01" class="form-control @error('min_risk_buy') is-invalid @enderror"
					id="min_risk_buy" name="min_risk_buy" value="{{ old('min_risk_buy', 0) }}" required placeholder="e.g., 0">
				@error('min_risk_buy')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="max_risk_buy" class="form-label">Max Risk Buy *</label>
				<div class="form-text">
					If risk metric is less or equal to this value, then DCA will be executed. Risk metric is ranging from 0.00 to
					100.00.
				</div>
				<input type="number" step="0.01" class="form-control @error('max_risk_buy') is-invalid @enderror"
					id="max_risk_buy" name="max_risk_buy" value="{{ old('max_risk_buy', 100) }}" required
					placeholder="e.g., 99.99">
				@error('max_risk_buy')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="min_buy_amount" class="form-label">Min Buy Amount *</label>
				<div class="form-text">
					Adjusted buy amount in fiat won't be less than this. Set to 0 to disable.
				</div>
				<input type="number" class="form-control @error('min_buy_amount') is-invalid @enderror" id="min_buy_amount"
					name="min_buy_amount" value="{{ old('min_buy_amount', 0) }}" required placeholder="Enter min buy amount">
				@error('min_buy_amount')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="max_buy_amount" class="form-label">Max Buy Amount *</label>
				<div class="form-text">
					Adjusted buy amount in fiat won't be more than this. Set to 0 to disable.
				</div>
				<input type="number" class="form-control @error('max_buy_amount') is-invalid @enderror" id="max_buy_amount"
					name="max_buy_amount" value="{{ old('max_buy_amount', 0) }}" required placeholder="Enter max buy amount">
				@error('max_buy_amount')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="description" class="form-label">Description</label>
				<textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
				 rows="3" placeholder="Optional description...">{{ old('description') }}</textarea>
				@error('description')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="is_active" class="form-label">Is Active *</label>
				<select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
					<option value="1" @selected(old('is_active') == 1)>Yes</option>
					<option value="0" @selected(old('is_active') == 0)>No</option>
				</select>
				@error('is_active')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<!-- Extra Buys Section -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">
						<input type="checkbox" class="form-check-input me-2" id="enable_extra_buys" name="enable_extra_buys"
							value="1" @checked(old('enable_extra_buys'))>
						<label for="enable_extra_buys" class="form-label mb-0">Enable Extra Buys</label>
					</h5>
					<div class="form-text">
						Allow this schedule to execute additional buys outside of its regular schedule when risk conditions change.
						<strong>Note:</strong> When enabled, the regular schedule fields above will be hidden to avoid confusion.
						When you use extra buys, make sure cron <code>my:reset-extra-buys-counters</code> runs properly because it will
						reset the
						extra buys counter on the predefined interval.
					</div>
				</div>
				<div class="card-body" id="extra_buys_settings">
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label for="extra_buys_reset_interval" class="form-label">Reset Interval</label>
								<div class="form-text">
									<strong>Recommended:</strong> Choose the same interval as your main schedule above.<br>
									• Monthly schedule → Monthly reset<br>
									• Weekly schedule → Weekly reset<br>
									• Daily schedule → Daily reset<br>
									This ensures your extra buys reset in sync with your regular DCA cycle.
								</div>
								<select class="form-select @error('extra_buys_reset_interval') is-invalid @enderror"
									id="extra_buys_reset_interval" name="extra_buys_reset_interval">
									<option value="daily" @selected(old('extra_buys_reset_interval') == 'daily')>Daily</option>
									<option value="weekly" @selected(old('extra_buys_reset_interval') == 'weekly')>Weekly</option>
									<option value="monthly" @selected(old('extra_buys_reset_interval') == 'monthly')>Monthly</option>
								</select>
								@error('extra_buys_reset_interval')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label for="max_extra_buys_per_interval" class="form-label">Max Extra Buys Per Interval</label>
								<div class="form-text">
									Maximum number of extra buys allowed within the reset interval.
								</div>
								<input type="number" class="form-control @error('max_extra_buys_per_interval') is-invalid @enderror"
									id="max_extra_buys_per_interval" name="max_extra_buys_per_interval"
									value="{{ old('max_extra_buys_per_interval', 1) }}" min="0" max="100" placeholder="e.g., 1">
								@error('max_extra_buys_per_interval')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label for="min_hours_between_extra_buys" class="form-label">Min Hours Between Extra Buys</label>
								<div class="form-text">
									Minimum hours required between extra buy executions.
								</div>
								<input type="number" class="form-control @error('min_hours_between_extra_buys') is-invalid @enderror"
									id="min_hours_between_extra_buys" name="min_hours_between_extra_buys"
									value="{{ old('min_hours_between_extra_buys', 1) }}" min="1" max="168" placeholder="e.g., 1">
								@error('min_hours_between_extra_buys')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label for="reset_mode" class="form-label">Reset Mode</label>
								<div class="form-text">
									<strong>Relative (Recommended):</strong> Reset counter after the interval period from your last extra buy.<br>
									<strong>Absolute:</strong> Reset counter on calendar boundaries (requires reliable cron timing).<br>
									<small class="text-muted">
										• <strong>Relative:</strong> More reliable, self-healing, no cron dependency<br>
										• <strong>Absolute:</strong> Calendar-aligned but requires precise cron timing<br>
										• Daily: Relative (24h after last buy) vs Absolute (00:00 daily)<br>
										• Weekly: Relative (7 days after last buy) vs Absolute (Monday 00:00)<br>
										• Monthly: Relative (30 days after last buy) vs Absolute (1st of month 00:00)
									</small>
								</div>
								<select class="form-select @error('reset_mode') is-invalid @enderror" id="reset_mode" name="reset_mode">
									<option value="1" @selected(old('reset_mode', 1) == 1)>Relative (recommended) - Reset after interval from last
										extra buy</option>
									<option value="2" @selected(old('reset_mode') == 2)>Absolute - Reset on calendar boundaries (requires reliable
										cron)</option>
								</select>
								@error('reset_mode')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>
				</div>
			</div>

			<button type="submit" class="btn btn-primary">Save</button>
			<a href="{{ route('dcaschedules.index') }}" class="btn btn-secondary">Cancel</a>
		</form>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const enableExtraBuys = document.getElementById('enable_extra_buys');
			const extraBuysSettings = document.getElementById('extra_buys_settings');
			const scheduledEvery = document.getElementById('scheduled_every');
			const resetInterval = document.getElementById('extra_buys_reset_interval');
			const scheduledEveryGroup = document.getElementById('scheduled_every_group');
			const scheduledEveryOptionGroup = document.getElementById('scheduled_every_option_group');

			function toggleExtraBuysSettings() {
				const minHoursField = document.getElementById('min_hours_between_extra_buys');
				const maxBuysField = document.getElementById('max_extra_buys_per_interval');
				const resetIntervalField = document.getElementById('extra_buys_reset_interval');
				const resetModeField = document.getElementById('reset_mode');

				if (enableExtraBuys.checked) {
					extraBuysSettings.style.display = 'block';
					// Hide scheduled every fields when extra buys is enabled
					scheduledEveryGroup.style.display = 'none';
					scheduledEveryOptionGroup.style.display = 'none';
					// Enable validation for extra buys fields
					minHoursField.removeAttribute('disabled');
					maxBuysField.removeAttribute('disabled');
					resetIntervalField.removeAttribute('disabled');
					resetModeField.removeAttribute('disabled');
				} else {
					extraBuysSettings.style.display = 'none';
					// Show scheduled every fields when extra buys is disabled
					scheduledEveryGroup.style.display = 'block';
					scheduledEveryOptionGroup.style.display = 'block';
					// Disable validation for extra buys fields when hidden
					minHoursField.setAttribute('disabled', 'disabled');
					maxBuysField.setAttribute('disabled', 'disabled');
					resetIntervalField.setAttribute('disabled', 'disabled');
					resetModeField.setAttribute('disabled', 'disabled');
				}
			}

			function suggestResetInterval() {
				const scheduleValue = scheduledEvery.value;
				let suggestedInterval = 'daily'; // default

				if (scheduleValue.includes('monthly')) {
					suggestedInterval = 'monthly';
				} else if (scheduleValue.includes('weekly')) {
					suggestedInterval = 'weekly';
				} else if (scheduleValue.includes('daily')) {
					suggestedInterval = 'daily';
				}

				// Only suggest if reset interval is not already set
				if (resetInterval.value === 'daily') {
					resetInterval.value = suggestedInterval;
				}
			}

			enableExtraBuys.addEventListener('change', toggleExtraBuysSettings);
			scheduledEvery.addEventListener('change', suggestResetInterval);

			toggleExtraBuysSettings(); // Initial state
			suggestResetInterval(); // Initial suggestion
		});
	</script>
@endsection
