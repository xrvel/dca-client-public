@extends('layouts.app')

@section('html_title')
	Risk Simulation
@endsection

@section('content')
	<h1>Risk Simulation</h1>

	<p>Simulate your risk by filling the form below</p>
	<form id="simulationForm">
		<!-- DCA Amount -->
		<div class="mb-3">
			<label for="amount" class="form-label">DCA Amount (e.g. in USD) *</label>
			<input type="number" class="form-control" id="amount" name="amount" min="1" max="1000000000" required
				value="100" autofocus autocomplete="off">
		</div>

		<!-- Algorithm Select -->
		<div class="mb-3">
			<label for="algorithm" class="form-label">Buy Strategy</label>
			<select class="form-select" id="algorithm" name="algorithm" required>
				<option value="fixed">Fixed</option>
				<option value="linear">Dynamic Linear</option>
				<option value="log_1">Dynamic Logarithmic 1</option>
				<option value="log_2">Dynamic Logarithmic 2 (aggressive)</option>
				<option value="log_low_1">Dynamic Logarithm - Low Threshold 1</option>
				<option value="square_1">Squared 1 (aggressive)</option>
			</select>
		</div>

		<!-- Submit Button -->
		<div class="d-grid">
			<button type="submit" class="btn btn-primary">Run Simulation</button>
		</div>

	</form>

	<div id="risk_result" class="mt-4"></div>
@endsection

@push('footer_code')
	<script>
		document.getElementById('simulationForm').addEventListener('submit', function(e) {
			e.preventDefault();

			const form = e.target;
			const formData = new FormData(form);
			const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

			console.log(formData);

			fetch('<?php echo route('api.risk.simulation'); ?>', {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': csrfToken,
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'text/html'
					},
					body: formData
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
		});
	</script>
@endpush
