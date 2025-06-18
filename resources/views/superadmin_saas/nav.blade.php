

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <script>
        var navbarStyle = window.config.config.phoenixNavbarStyle;
        if (navbarStyle && navbarStyle !== 'transparent') {
            document.querySelector('body').classList.add(`navbar-${navbarStyle}`);
        }
    </script>

    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHorizontalCollapse"
                aria-controls="navbarHorizontalCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarHorizontalCollapse">
            <ul class="navbar-nav" id="navbarHorizontalNav">
                <li class="nav-item">
                    <a class="nav-link label-1 {{ Request::is('superadmin') ? 'active' : '' }}" href="/superadmin" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fa fa-user"></span></span>
                            <span class="nav-link-text ms-2">SuperAdmin</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link label-1 {{ Request::is('*businesses*') ? 'active' : '' }}" href="/superadmin/businesses" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fa fa-users"></span></span>
                            <span class="nav-link-text ms-2">Businesses</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link label-1 {{ Request::is('superadmin/subscriptions') ? 'active' : '' }}" href="{{ route('superadmin.subscriptions') }}" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fa fa-google-wallet"></span></span>
                            <span class="nav-link-text ms-2">Subscriptions</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link label-1 {{ Request::is('*packages*') ? 'active' : '' }}" href="{{ route('superadmin.packages') }}" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fa fa-user"></span></span>
                            <span class="nav-link-text ms-2">Packages</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link label-1 {{ Request::is('superadmin/settings') ? 'active' : '' }}" href="{{ route('superadmin.settings') }}" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fa fa-cogs"></span></span>
                            <span class="nav-link-text ms-2">Settings</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link label-1 {{ Request::is('superadmin/communicator') ? 'active' : '' }}" href="{{ route('superadmin.communicator') }}" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fa fa-comments"></span></span>
                            <span class="nav-link-text ms-2">Communicator</span>
                        </div>
                    </a>
                </li>

                <!-- Add more nav items here as needed -->
            </ul>
        </div>
    </div>
</nav>
