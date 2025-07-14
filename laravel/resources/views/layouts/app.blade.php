<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>@yield('html_title', 'Welcome') - DCA Client</title>
	<!-- add favicon -->
	<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"
		integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg=="
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<style>
		@media (max-width: 767.98px) {

			/* Below 'lg' breakpoint */
			.navbar-collapse {
				position: absolute;
				top: 100%;
				left: 0;
				right: 0;
				background-color: #343a40;
				/* Same as navbar-dark bg-dark */
				z-index: 1000;
				padding: 1rem;
			}
		}
	</style>

	@stack('header_code')
</head>

<body>
	<!-- Navbar -->
	<nav class="navbar navbar-expand-md navbar-dark bg-dark position-relative">
		<div class="container-fluid">
			<a class="navbar-brand" href="{{ route('home') }}">DCA Client</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
				aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="mainNavbar">
				<ul class="navbar-nav me-auto mb-2 mb-lg-0">
					<li class="nav-item">
						<a class="nav-link" aria-current="page" href="{{ route('home') }}">Home</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('dcaschedules.index') }}">DCA Schedules</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('get.dca_history') }}">DCA History</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('dcakeys.index') }}">Exchange Keys</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('risk.simulation') }}">Risk Simulation</a>
					</li>
					<!--
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
							aria-expanded="false">
							More
						</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="#">Action</a></li>
							<li><a class="dropdown-item" href="#">Another action</a></li>
							<li>
								<hr class="dropdown-divider">
							</li>
							<li><a class="dropdown-item" href="#">Something else</a></li>
						</ul>
					</li>
					-->
				</ul>
				<div class="d-flex gap-2">
					@guest
					<a href="{{ route('login') }}" class="btn btn-outline-light">Login</a>
					@if (false)
					<a href="{{ route('register') }}" class="btn btn-light text-dark">Register</a>
					@endif
					@endguest

					@auth
					<a class="btn btn-light text-dark" href="{{ route('logout') }}" rel="noopener" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
					<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
						@csrf
					</form>
					@endauth
				</div>

			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="container mt-4">
		@yield('content')
	</div>

	<div class="container mt-4">
		<div class="row">
			<div class="col text-center">
				<p class="text-muted"><a href="https://alphasquared.io/" target="_blank" rel="noopener">Alphasquared</a> DCA Client. <a href="https://github.com/xrvel/dca-client-public" target="_blank" rel="noopener">Github</a></p>
			</div>
		</div>
	</div>

	</div>

	@stack('footer_code')

	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>