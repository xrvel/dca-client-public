{{-- resources/views/mydcakeys/create.blade.php --}}
@extends('layouts.app')

@section('html_title')
Create Exchange Key
@endsection

@section('content')
<div class="container mt-4">

	<h1 class="mb-4">Create New Exchange Key</h1>

	@if ($errors->any())
	<div class="alert alert-danger">
		<ul>
			@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
	@endif

	<form action="{{ route('dcakeys.store') }}" method="POST">
		@csrf

		<div class="mb-3">
			<label for="label" class="form-label">Label *</label>
			<input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label" value="{{ old('label') }}" autofocus required placeholder="Label...">
			@error('label')
			<div class="invalid-feedback">{{ $message }}</div>
			@enderror
		</div>

		<div class="mb-3">
			<label for="exchange_name" class="form-label">Exchange Name *</label>
			<select class="form-control @error('exchange_name') is-invalid @enderror" id="exchange_name" name="exchange_name">
				@foreach ($exchanges as $key => $exchange)
				<option value="{{ $key }}" @selected(old('exchange_name') == $key)>{{ ucwords($exchange) }}</option>
				@endforeach
			</select>
			@error('exchange_name')
			<div class="invalid-feedback">{{ $message }}</div>
			@enderror
		</div>

		<div class="mb-3">
			<label for="api_key" class="form-label">API Key *</label>
			<input type="text" class="form-control @error('api_key') is-invalid @enderror" id="api_key" name="api_key" value="{{ old('api_key') }}" required placeholder="API Key...">
			@error('api_key')
			<div class="invalid-feedback">{{ $message }}</div>
			@enderror
		</div>

		<div class="mb-3">
			<label for="api_secret" class="form-label">API Secret *</label>
			<input type="text" class="form-control @error('api_secret') is-invalid @enderror" id="api_secret" name="api_secret" value="{{ old('api_secret') }}" required placeholder="API Secret...">
			@error('api_secret')
			<div class="invalid-feedback">{{ $message }}</div>
			@enderror
		</div>

		<button type="submit" class="btn btn-primary">Save</button>
		<a href="{{ route('dcakeys.index') }}" class="btn btn-secondary">Cancel</a>
	</form>
</div>
@endsection