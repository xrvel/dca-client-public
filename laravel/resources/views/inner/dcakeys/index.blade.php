{{-- resources/views/mydcakeys/index.blade.php --}}
@extends('layouts.app')

@section('html_title')
	Exchange Keys
@endsection

@section('content')
	<div class="container mt-4">
		<h1 class="mb-4">Exchange Keys <a href="{{ route('dcakeys.create') }}" class="btn btn-outline-primary">Create</a></h1>

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
					<th scope="col" class="text-center">Exchange</th>
					<th scope="col" class="text-center">API Key</th>
					<th scope="col" class="text-center">Actions</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($dcakeys as $dcakey)
					<tr valign="top">
						<td class="text-end">{{ $dcakey->id }}</td>
						<td>{{ $dcakey->label }}</td>
						<td>
							{{ $dcakey->exchange_name }}
							@if ('binance_proxy' == $dcakey->exchange_name)
								<div>
									<a href="{{ route('binance.proxy', $dcakey->id) }}" class="btn btn-sm btn-secondary" target="_blank">See Proxy
										File</a>
								</div>
							@endif
						</td>
						<td>{{ Str::limit($dcakey->api_key, 10, '...') }}</td>
						<td>
							<a href="{{ route('dcakeys.edit', $dcakey->id) }}" class="btn btn-sm btn-warning">Edit</a>
							<form action="{{ route('dcakeys.destroy', $dcakey->id) }}" method="POST" class="d-inline"
								onsubmit="return confirm('Are you sure you want to delete this key?');">
								@csrf
								@method('DELETE')
								<button class="btn btn-sm btn-danger" type="submit">Delete</button>
							</form>
							<input type="button" value="Test Connection" class="btn btn-sm btn-info"
								onclick="my_key_check_connection('{{ $dcakey->id }}')">
							<div id="risk_result_{{ $dcakey->id }}" class="mt-4"></div>
							<hr />
							<div class="text-muted">
								{{ $dcakey->created_at }}
							</div>
							@if ($dcakey->ok_last_check_timestamp)
								<div class="text-success">
									Last OK : {{ date('r', $dcakey->ok_last_check_timestamp) }}
								</div>
							@endif
							@if ($dcakey->error_last_check_timestamp)
								<div class="text-danger">
									Last Error : {{ date('r', $dcakey->error_last_check_timestamp) }}
								</div>
							@endif
						</td>
					</tr>
				@endforeach

				@if ($dcakeys->isEmpty())
					<tr>
						<td colspan="7" class="text-center">No records found.</td>
					</tr>
				@endif
			</tbody>
		</table>

		<div class="d-flex justify-content-center">
			{{ $dcakeys->links() }}
		</div>
	</div>
@endsection

@push('footer_code')
	<script>
		function my_key_check_connection(id) {
			const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

			fetch(`{{ route('api.key.check') }}`, {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': csrfToken,
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'text/html',
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: 'key_id=' + id
				})
				.then(async (response) => {
					const resultDiv = document.getElementById('risk_result_' + id);
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
					console.error('Error:', error);
				});
		}
	</script>
@endpush
