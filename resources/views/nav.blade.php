<style>
    .navbar {
        padding: 0;
    }
    
    .nav-link {
        transition: all 0.3s ease;
        border-radius: 4px;
        margin: 0 2px;
    }
    
    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
        font-weight: 500;
    }
    
    .nav-link-icon {
        width: 20px;
        text-align: center;
    }
    
    .navbar-brand {
        padding: 1rem;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Brand/logo can be added here -->
        <a class="navbar-brand me-4" href="#">
            <span class="fw-bold">Super Admin Panel</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHorizontalCollapse" aria-controls="navbarHorizontalCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarHorizontalCollapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link py-3 px-3 d-flex align-items-center" href="{{ route('superadmin.index') }}" role="button">
                        <span class="nav-link-icon me-2">
                            <i class="fas fa-user-shield"></i>
                        </span>
                        <span class="nav-link-text">SuperAdmin</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link py-3 px-3 d-flex align-items-center" href="{{ route('superadmin.businesses') }}" role="button">
                        <span class="nav-link-icon me-2">
                            <i class="fas fa-building"></i>
                        </span>
                        <span class="nav-link-text">Businesses</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-3 px-3 d-flex align-items-center" href="{{ route('superadmin.subscriptions') }}" role="button">
                        <span class="nav-link-icon me-2">
                            <i class="fas fa-credit-card"></i>
                        </span>
                        <span class="nav-link-text">Subscriptions</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-3 px-3 d-flex align-items-center" href="{{ route('superadmin.packages') }}" role="button">
                        <span class="nav-link-icon me-2">
                            <i class="fas fa-box-open"></i>
                        </span>
                        <span class="nav-link-text">Packages</span>
                    </a>
                </li>

            
                
                <li class="nav-item">
                    <a class="nav-link py-3 px-3 d-flex align-items-center" href="{{ route('superadmin.communicator') }}" role="button">
                        <span class="nav-link-icon me-2">
                            <i class="fas fa-comments"></i>
                        </span>
                        <span class="nav-link-text">Communicator</span>
                        <!--<span class="badge bg-danger ms-2">3</span>-->
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-3 px-3 d-flex align-items-center" href="{{ route('superadmin.settings') }}" role="button">
                        <span class="nav-link-icon me-2">
                            <i class="tf-icons bx bx-cog"></i>
                        </span>
                        <span class="nav-link-text">Settings</span>
                    </a>
                </li>

            </ul>
            
        </div>
    </div>
</nav>

@section('page-script')

    <script>
        // Highlight active nav item based on current route
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                    link.setAttribute('aria-current', 'page');
                }
            });

            
        });
    </script>
@endsection
