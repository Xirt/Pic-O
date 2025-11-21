<header class=" sticky-top">
@auth
<nav class="navbar navbar-expand-lg bg-body-tertiary">

    <div class="container-fluid">

        <a href="{{ route('home') }}" class="navbar-brand me-3">
            <img src="{{ asset('favicon.svg') }}" alt="Icon">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav me-auto">

                @can('viewAny', \App\Models\Photo::class)
                <li class="nav-item me-3">
                    <a class="nav-link" href="{{ route('home') }}">
                        <i class="bi bi-calendar-week me-2"></i>
                        Timeline
                    </a>
                </li>
                @endcan

                @can('viewAny', \App\Models\Album::class)
                <li class="nav-item me-3">
                    <a class="nav-link" href="{{ route('albums.index') }}">
                        <i class="bi bi-images me-2"></i>
                        Albums
                    </a>
                </li>
                @endcan

                @can('viewAny', \App\Models\Folder::class)
                <li class="nav-item me-3">
                    <a class="nav-link" href="{{ route('folders.index') }}">
                        <i class="bi bi-folder2 me-2"></i>
                        Folders
                    </a>
                </li>
                @endcan
            </ul>

            <ul class="navbar-nav">

                @auth

                @if(auth()->user()->role->value === 'admin')
                <li class="nav-item me-2">
                    <a class="nav-link" href="{{ route('admin.index') }}">
                        <i class="bi bi-gear me-2 me-lg-0"></i>
                        <span class="d-lg-none">Administration</span>
                    </a>
                </li>
                @endif

                <li class="nav-item me-2">
                    <a class="nav-link" href="#" data-bs-toggle="offcanvas-update-user" data-user-id="{{ auth()->user()->id }}">
                        <i class="bi bi-person-circle me-2 me-lg-0"></i>
                        <span class="d-lg-none">Account</span>
                    </a>
                </li>

                <li class="nav-item me-2">
                    <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-2 me-lg-0"></i>
                        <span class="d-lg-none">Logout</span>
                    </a>
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