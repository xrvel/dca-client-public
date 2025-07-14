{{-- resources/views/mydcaschedules/index.blade.php --}}
@extends('layouts.app')

@section('html_title')
	DCA Schedules
@endsection

@section('content')
	<div class="container mt-4">
		<h1 class="mb-4">DCA Schedules <a href="{{ route('dcaschedules.create') }}" class="btn btn-outline-primary">Create</a>
		</h1>

		@if (session('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				{{ session('success') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		@if (session('error'))
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				{{ session('error') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		<table class="table table-bordered table-hover align-middle">
			<thead class="table-light">
				<tr valign="top">
					<th scope="col" class="text-center">ID</th>
					<th scope="col" class="text-center">Label</th>
					<th scope="col" class="text-center">Pair / Exchange</th>
					<th scope="col" class="text-center">Strategy</th>
					<th scope="col" class="text-center">Base Amount (fiat)</th>
					<th scope="col" class="text-center">Is Active</th>
					<th scope="col" class="text-center">Actions</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($dcaschedules as $dcaschedule)
					<tr valign="top">
						@php
							$currency = my_guess_currency($dcaschedule->pair_name);
						@endphp
						<td class="text-end">{{ $dcaschedule->id }}</td>
						<td>{{ $dcaschedule->label }}</td>
						<td>
							{{ $dcaschedule->pair_name }}
							<br />

							@if ($dcaschedule->dca_key)
								<a href="{{ route('dcakeys.edit', $dcaschedule->dca_key->id) }}" target="_blank">
									{{ $dcaschedule->dca_key->label }}
									-
									{{ $dcaschedule->dca_key->exchange_name }}
								</a>
							@else
								{{ $dcaschedule->key_id }}
							@endif
						</td>
						<td>
							@if (!$dcaschedule->enable_extra_buys)
								Scheduled : {{ ucwords($dcaschedule->scheduled_every) }}

								@if ('custom' == $dcaschedule->scheduled_every)
									<div>see &quot;Custom Schedule&quot; button</div>
								@elseif ($dcaschedule->scheduled_every_option != '')
									- {{ $dcaschedule->scheduled_every_option }}
								@endif
								<br />
							@endif
							Strategy : <a href="{{ route('risk.simulation') }}" target="_blank">{{ $dcaschedule->buy_strategy }}</a><br />
							@if (0 == $dcaschedule->min_risk_buy && 100 == $dcaschedule->max_risk_buy)
								<span class="text-muted">All {{ $dcaschedule->risk_symbol }} risk value</span><br />
							@else
								Active : <strong>{{ $dcaschedule->min_risk_buy }}</strong> &le; {{ $dcaschedule->risk_symbol }} risk &le;
								<strong>{{ $dcaschedule->max_risk_buy }}</strong><br />
								Current risk : <strong>{{ $risk_symbols[$dcaschedule->risk_symbol] }}</strong><br />
							@endif

							@if ($dcaschedule->min_buy_amount > 0)
								<div>Min {{ $currency }} {{ number_format($dcaschedule->min_buy_amount, 2) }}</div>
							@endif

							@if ($dcaschedule->max_buy_amount > 0)
								<div>Max {{ $currency }} {{ number_format($dcaschedule->max_buy_amount, 2) }}</div>
							@endif

							<input type="button" value="Simulation Info" class="btn btn-sm btn-info"
								onclick="my_show_dialog('modal_simulation_{{ $dcaschedule->id }}', <?php echo $dcaschedule->id; ?>, '<?php echo strtolower($dcaschedule->risk_symbol); ?>')">
							<div class="d-none" id="modal_simulation_{{ $dcaschedule->id }}">
								<?php
								for ($i = 1; $i <= 100; $i++) {
								    $dca_output = my_get_final_dca_output([
								        'risk' => $i,
								        'base_amount' => $dcaschedule->base_amount,
								        'algorithm' => $dcaschedule->buy_strategy,
								    ]);
								
								    echo '<div id="id_div_risk_' . $dcaschedule->id . '_' . $i . '"';
								    if ($i < $dcaschedule->min_risk_buy || $i > $dcaschedule->max_risk_buy) {
								        echo ' class="text-decoration-line-through text-muted"';
								    }
								    echo '>Risk = ' . $i . ', Final amount: ' . $currency . ' ' . number_format($dcaschedule->base_amount) . ' x ' . $dca_output['buy_risk'] . '% x ' . $dca_output['multiplier'] . ' = ';
								    if ($dcaschedule->min_buy_amount > 0 && $dca_output['final_amount'] < $dcaschedule->min_buy_amount) {
								        echo '<span class="text-decoration-line-through text-muted">' . $currency . ' ' . number_format($dca_output['final_amount'], 2) . '</span> ' . $currency . ' ' . number_format($dcaschedule->min_buy_amount, 2);
								    } elseif ($dcaschedule->max_buy_amount > 0 && $dca_output['final_amount'] > $dcaschedule->max_buy_amount) {
								        echo '<span class="text-decoration-line-through text-muted">' . $currency . ' ' . number_format($dca_output['final_amount'], 2) . '</span> ' . $currency . ' ' . number_format($dcaschedule->max_buy_amount, 2);
								    } else {
								        echo $currency . ' ' . number_format($dca_output['final_amount'], 2);
								    }
								    echo '<span id="id_risk_' . $dcaschedule->id . '_' . $i . '"></span>';
								    echo '</div>';
								}
								?>
							</div>

							<button type="button" class="btn btn-primary btn-sm"
								onclick="my_check_risk('<?php echo strtolower($dcaschedule->risk_symbol); ?>', <?php echo $dcaschedule->id; ?>)">Check risk</button>

							<span id="risk_result_{{ $dcaschedule->id }}" class="mt-4"></span>

							@if ($dcaschedule->enable_extra_buys)
								<div class="text-muted">
									This will run extra buy(s) max <strong>{{ $dcaschedule->max_extra_buys_per_interval }}</strong> times
									{{ $dcaschedule->extra_buys_reset_interval }}.
									<div>
										Interval between extra buys : {{ $dcaschedule->min_hours_between_extra_buys }} hours
									</div>
									@if ($dcaschedule->last_extra_buy_timestamp > 0)
										<div>
											Last extra buy : {{ date('m/d H:i', $dcaschedule->last_extra_buy_timestamp) }}
										</div>
									@endif
									<div>
										Reset mode :
										@if ($dcaschedule->reset_mode == 1)
											Relative
										@else
											Absolute
										@endif
									</div>
									<div>
										Extra buys executed : <span title="Extra buys executed"
											style="cursor:help">{{ $dcaschedule->extra_buys_executed_count }}</span>
									</div>
									<div>
										Extra buys will be attempted every 1 hour, assuming all conditions are met.
									</div>
								</div>
							@endif
						</td>
						<td class="text-end">
							{{ $currency }} {{ number_format($dcaschedule->base_amount, 2) }}
							@if ($dcaschedule->enable_extra_buys)
								<div>
									<span class="badge bg-success">Extra Buys</span>
								</div>
							@endif
						</td>
						<td class="{{ $dcaschedule->is_active ? 'table-success' : '' }}">
							{{ $dcaschedule->is_active ? 'Yes' : 'No' }}
						</td>
						<td>
							<a href="{{ route('dcaschedules.edit', $dcaschedule->id) }}" class="btn btn-sm btn-warning">Edit</a>
							<form action="{{ route('dcaschedules.destroy', $dcaschedule->id) }}" method="POST" class="d-inline"
								onsubmit="return confirm('Are you sure you want to delete this schedule?');">
								@csrf
								@method('DELETE')
								<button class="btn btn-sm btn-danger" type="submit">Delete</button>
							</form>
							@if ('custom' == $dcaschedule->scheduled_every)
								<input type="button" value="Custom Schedule" class="btn btn-sm btn-info"
									onclick="my_show_dialog('modal_command_{{ $dcaschedule->id }}', 0, '')">
							@endif
							<div class="d-none" id="modal_command_{{ $dcaschedule->id }}">
								To use custom Laravel scheduler, please add this line to your <code>routes/console.php</code> file:<br />
								<code class="user-select-all">Schedule::command('my:run-dca
									--id={{ $dcaschedule->id }}')->dailyAt('13:00')->withoutOverlapping(2);</code><br />
								Or you can run this command directly:<br />
								<code class="user-select-all">php artisan my:run-dca --id={{ $dcaschedule->id }}</code>
							</div>
							<hr />
							<div class="text-muted">
								{{ $dcaschedule->created_at }}
							</div>
						</td>
					</tr>
				@endforeach

				@if ($dcaschedules->isEmpty())
					<tr>
						<td colspan="10" class="text-center">No records found.</td>
					</tr>
				@endif
			</tbody>
		</table>

		<div class="d-flex justify-content-center">
			{{ $dcaschedules->links() }}
		</div>
	</div>

	<div class="modal fade" id="myModal_generic" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
		aria-labelledby="staticBackdropLabel" aria-hidden="true" role="dialog">
		<div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Message</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
					</button>
				</div>
				<div class="modal-body" id="modal_generic_message">
					?
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('footer_code')
	@foreach ($risk_symbols as $symbol => $risk)
		<input type="hidden" id="my_risk_{{ strtolower($symbol) }}" value="{{ $risk }}" readonly />
	@endforeach

	<script>
		function my_check_risk(_symbol, _schedule_id) {
			const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

			fetch('<?php echo route('api.risk.get'); ?>', {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': csrfToken,
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'text/html',
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: 'symbol=' + _symbol
				})
				.then(async (response) => {
					const resultDiv = document.getElementById('risk_result_' + _schedule_id);
					if (!response.ok) {
						const errorText = await response.text();
						resultDiv.innerHTML =
							`<div class="alert alert-danger">Error ${response.status}: ${errorText}</div>`;
						return;
					}

					const html = await response.text();
					resultDiv.innerHTML = html;
					document.getElementById('my_risk_' + _symbol).value = html;
				})
				.catch(error => {
					document.getElementById('risk_result').innerHTML =
						`<div class="alert alert-danger">AJAX Error: ${error.message}</div>`;
				});
		}

		function my_show_dialog(source_modal_message_id, _schedule_id, _symbol) {
			if (0 != _schedule_id && '' != _symbol) {
				var the_risk_0 = document.getElementById('my_risk_' + _symbol).value;
				var the_risk_1 = parseInt(the_risk_0);
				var the_risk_2 = the_risk_1 + 1;

				for (i = 1; i <= 100; i++) {
					var riskElement = document.getElementById('id_risk_' + _schedule_id + '_' + i);
					if (riskElement) {
						if (i == the_risk_1 || i == the_risk_2) {
							document.getElementById('id_div_risk_' + _schedule_id + '_' + i).classList.add('bg-info');
							riskElement.innerHTML = ' <strong>Current risk (' + the_risk_0 + ')</strong>';
						} else {
							document.getElementById('id_div_risk_' + _schedule_id + '_' + i).classList.remove('bg-info');
							riskElement.innerHTML = '';
						}
					}
				}
			}

			// Get the source content
			var content = document.getElementById(source_modal_message_id)?.innerHTML;

			if (content !== undefined) {
				// Insert it into the modal body
				document.getElementById("modal_generic_message").innerHTML = content;

				// Show the modal using Bootstrap 5's modal API
				var modal = new bootstrap.Modal(document.getElementById('myModal_generic'));
				modal.show();
			} else {
				console.error("Element with ID '" + source_modal_message_id + "' not found.");
			}
		}
	</script>
@endpush
