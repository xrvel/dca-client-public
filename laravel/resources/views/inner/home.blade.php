@extends('layouts.app')

@section('html_title')
	Home
@endsection

@section('content')
	<h1>Setting Check</h1>
	@auth
		<ol>
			<li>
				Alphasquared API Key :
				@if ($dca_api_key_ok)
					<span class="badge text-bg-success">Valid</span>

					<button type="button" class="btn btn-primary btn-sm" onclick="my_check_risk()">Check risk</button>

					<span id="risk_result" class="mt-4"></span>
				@else
					<span class="badge text-bg-danger">Invalid</span>, check <code>ALPHASQUARED_API_KEY</code> in your <code>.env</code>
					file.
				@endif
			</li>
			<li>
				Exchange Keys :
				@if ($dca_key_count > 0)
					<span class="badge text-bg-success">OK</span> : <a
						href="{{ route('dcakeys.index') }}">{{ number_format($dca_key_count) }} keys</a> detected.
				@else
					<span class="badge text-bg-danger">Empty</span> <a href="{{ route('dcakeys.create') }}">add exchange keys</a>.
				@endif
			</li>
			<li>
				DCA Schedule :
				@if ($dca_schedule_count > 0)
					<span class="badge text-bg-success">OK</span> : <a
						href="{{ route('dcaschedules.index') }}">{{ number_format($dca_schedule_count) }} schedules</a> detected.
				@else
					<span class="badge text-bg-danger">Empty</span> <a href="{{ route('dcaschedules.create') }}">add dca schedule</a>.
				@endif
			</li>
			<li>
				DCA History : <a href="{{ route('get.dca_history') }}">{{ number_format($dca_history_count) }} records</a>
			</li>
			<li>
				If you use <code>binance_proxy</code> exchange, make sure you :
				<ol>
					<li>have set <code>DCA_BINANCE_PROXY_URL</code> in <code>.env</code> (current remote proxy URL is
						<code>{{ env('DCA_BINANCE_PROXY_URL') }}</code>) and</li>
					<li>have proxy file in your server</li>
				</ol>
				If you want to connect to Binance, and your computer (or your server) is not supported on Binance supported countries,
				you can place a custom file in Binance supported countries to act as a &quot;proxy&quot; or &quot;hop&quot; between
				your computer / server to Binance server.<br />
				Your computer (or your server) &harr; proxy file &harr; Binance server<br />
				Proxy file can be downloaded on <a href="{{ route('dcakeys.index') }}">Exchange Keys</a> page.
			</li>
		</ol>
	@endauth

	@guest
		<p>To use this application, you need to login first. Login information is in <code>.env</code> file.</p>
	@endguest

	<p>Don't forget to run this command in your computer / server to make sure the schedule will be executed properly</p>
	<code class="user-select-all">php artisan schedule:work</code>
@endsection

@push('footer_code')
	<script>
		function my_check_risk() {
			const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

			fetch('<?php echo route('api.risk.get'); ?>', {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': csrfToken,
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'text/html',
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: '1=1'
				})
				.then(async (response) => {
					const resultDiv = document.getElementById('risk_result');
					if (!response.ok) {
						const errorText = await response.text();
						resultDiv.innerHTML =
							`<div class="alert alert-danger">Error ${response.status}: ${errorText}</div>`;
						return;
					}

					const html = await response.text();
					resultDiv.innerHTML = html;
				})
				.catch(error => {
					document.getElementById('risk_result').innerHTML =
						`<div class="alert alert-danger">AJAX Error: ${error.message}</div>`;
				});
		}
	</script>
@endpush
