<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <script>
        var navbarStyle = window.config.config.phoenixNavbarStyle;
        if (navbarStyle && navbarStyle !== 'transparent') {
            document.querySelector('body').classList.add(`navbar-${navbarStyle}`);
        }
    </script>
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHorizontalCollapse" aria-controls="navbarHorizontalCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarHorizontalCollapse">
            <ul class="navbar-nav flex-row ms-auto" id="navbarHorizontalNav">
                <li class="nav-item">
                    <a class="nav-link label-1 {{ Route::is('superadmin.index') ? 'active' : '' }}" href="{{ route('superadmin.index') }}" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fa fa-user"></span>
                            </span>
                            <span class="nav-link-text ms-2">SuperAdmin</span>
                        </div>
                    </a>
                </li>
                <!-- Add more nav items here as needed -->
            </ul>
        </div>
    </div>
</nav>
