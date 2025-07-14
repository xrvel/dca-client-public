@extends('layouts.app')

@section('html_title')
Login
@endsection

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12 col-lg-6 mx-auto">
			<h1 class="text-center mb-5">Login</h1>

			<!-- Session Status -->
			<x-auth-session-status class="mb-4" :status="session('status')" />

			<form method="POST" action="{{ route('login') }}">
				@csrf

				<!-- Email Address -->
				<div class="form-floating mb-3">
					<input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" autofocus placeholder="Email..." minlength="3" required style="text-transform: lowercase">
					<label for="email" class="form-label">Email <span class="text-danger">*</span></label>

					@error('email')
					<div class="invalid-feedback">
						{{ $message }}
					</div>
					@enderror
				</div>

				<!-- Password -->
				<div class="mb-3">
					<label for="password" class="form-label">Password <span class="text-danger">*</span></label>
					<input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" minlength="8" required>

					@error('password')
					<div class="invalid-feedback">
						{{ $message }}
					</div>
					@enderror
				</div>

				<div class="mb-3 form-check">
					<input type="checkbox" class="form-check-input" id="remember" name="remember">
					<label class="form-check-label" for="remember">Remember me</label>
				</div>

				<button type="submit" class="btn btn-primary">Login</button>
			</form>
		</div>
	</div>
</div>
@endsection