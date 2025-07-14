{{-- resources/views/mydcakeys/index.blade.php --}}
@extends('layouts.app')

@section('html_title')
DCA History
@endsection

@section('content')
<div class="container mt-4">
	<h1 class="mb-4">DCA History</h1>

	@if(session('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		{{ session('success') }}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
	@endif

	@if(session('error'))
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		{{ session('error') }}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
	@endif

	<table class="table table-bordered table-hover align-middle">
		<thead class="table-light">
			<tr valign="top">
				<th scope="col" class="text-center">ID</th>
				<th scope="col" class="text-center">Strategy &amp; Key</th>
				<th scope="col" class="text-center">Risk</th>
				<th scope="col" class="text-center">Base Amount</th>
				<th scope="col" class="text-center">Final Amount</th>
				<th scope="col" class="text-center">Time</th>
			</tr>
		</thead>
		<tbody>
			@foreach($dca_history as $dcahistory)
			@php
			$currency = my_guess_currency($dcahistory->pair_name);
			@endphp
			<tr valign="top">
				<td class="text-end">{{ $dcahistory->id }}</td>
				<td>
					<div>
						@if ($dcahistory->dca_schedule)
						Schedule : <a href="{{ route('dcaschedules.edit', $dcahistory->schedule_id) }}">{{ $dcahistory->dca_schedule->label }}</a><br />
						@endif
						{{ $dcahistory->pair_name }},
						{{ $dcahistory->scheduled_every }},
						{{ $dcahistory->buy_strategy }}
					</div>

					@if ($dcahistory->dca_key)
					<div>
						Exchange key : <a href="{{ route('dcakeys.edit', $dcahistory->key_id) }}">{{ $dcahistory->dca_key->label }}</a><br />
						{{ $dcahistory->dca_key->exchange_name }}
					</div>
					@endif
				</td>
				<td class="text-end">
					{{ $dcahistory->risk_1 }}
				</td>
				<td class="text-end">
					{{ $currency }} {{ number_format($dcahistory->original_amount) }}
				</td>
				<td class="text-end">
					{{ $currency }} {{ number_format($dcahistory->adjusted_amount) }}
				</td>
				<td>
					{{ $dcahistory->created_at->timezone(env('APP_TIMEZONE'))->format('Y-m-d H:i:s') }}
				</td>
			</tr>
			@endforeach

			@if($dca_history->isEmpty())
			<tr>
				<td colspan="7" class="text-center">No records found.</td>
			</tr>
			@endif
		</tbody>
	</table>

	<div class="d-flex justify-content-center">
		{{ $dca_history->links() }}
	</div>
</div>
@endsection