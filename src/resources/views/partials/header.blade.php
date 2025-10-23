<header class=" sticky-top">
@auth
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a href="{{ route('home') }}" class="navbar-brand">
            <img src="{{ asset('favicon.svg') }}" alt="Icon">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                @auth

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('albums.index') }}">Albums</a>
                </li>

					@if(auth()->check() && auth()->user()->role->value === 'admin')
					<li class="nav-item">
						<a class="nav-link" href="{{ route('folders.index') }}">Folders</a>
					</li>

					<li class="nav-item">
						<a class="nav-link" href="{{ route('admin.index') }}">Admin</a>
					</li>
					@endif

				<li class="nav-item">
					<a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
				</li>
                @endauth

            </ul>
        </div>
    </div>
</nav>
@endauth

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
@csrf
</form>

</header>