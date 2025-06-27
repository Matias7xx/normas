<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-light" style="background: #1a1a1a; border-bottom: 3px solid #bea55a;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color: #bac1c9;">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        {{-- <li class="nav-item d-flex align-items-center ml-3">
            <img src="/images/brasao_pcpb.png" alt="Logo PCPB" width="35px" class="mr-2" style="opacity: .9"/>
            <div>
                <h4 class="mb-0 font-weight-bold" style="color: #fdfeff;">Biblioteca de Normas</h4>
                <small style="color: #6c757d;">Polícia Civil da Paraíba</small>
            </div>
        </li> --}}
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

        <!-- Fullscreen -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button" 
               data-toggle="tooltip" title="Tela cheia" style="color: #bac1c9;">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

        <!-- User Info -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
               id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
               style="color: #495057;">
                <div class="user-avatar mr-2 d-flex align-items-center justify-content-center rounded-circle" 
                     style="width: 32px; height: 32px; background: linear-gradient(45deg, #bea55a, #d4b86a);">
                    <i class="fas fa-user text-dark"></i>
                </div>
                <div class="d-none d-lg-block text-right">
                    <div class="font-weight-bold" style="font-size: 0.85rem; color: #c1c5c9;">
                        {{ Auth::user() ? Auth::user()->name : '' }}
                    </div>
                    <small style="font-size: 0.75rem; color: #b0b6bb;">
                        Mat: {{ Auth::user() ? Auth::user()->matricula : '' }}
                    </small>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow-lg border-0" 
                 style="background-color: #f8f9fa; border: 1px solid #bea55a !important;">
                <div class="dropdown-header border-bottom" style="color: #343a40; border-color: #bea55a !important;">
                    <strong>{{ Auth::user() ? Auth::user()->name : '' }}</strong><br>
                    <small style="color: #6c757d;">{{ Auth::user() ? Auth::user()->email : '' }}</small>
                </div>
                <div class="dropdown-divider" style="border-color: #dee2e6;"></div>
                <a class="dropdown-item" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   style="background-color: transparent; color: #495057;">
                    <i class="fas fa-sign-out-alt mr-2 text-danger"></i> Logout
                </a>
            </div>
        </li>
    </ul>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</nav>

<style>
/* Hover effects for navbar */
.navbar .nav-link:hover {
    color: #bea55a !important;
    transition: color 0.3s ease;
}

.dropdown-item:hover {
    background-color: rgba(190, 165, 90, 0.1) !important;
    color: #bea55a !important;
}

.navbar-search-block {
    background-color: rgba(248, 249, 250, 0.95) !important;
    border: 1px solid #bea55a;
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

/* Tooltip styling */
.tooltip-inner {
    background-color: #bea55a;
    color: #1a1a1a;
    font-weight: 500;
}

.tooltip.bs-tooltip-bottom .arrow::before {
    border-bottom-color: #bea55a;
}
</style>