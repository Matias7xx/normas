@php
    $fotoUsuario = null;
    if (Auth::check()) {
        $user = Auth::user();
        if ($user && $user->cpf) {
            $cpfLimpo = str_replace(['.', '-'], '', $user->cpf);
            $nomeArquivo = "{$cpfLimpo}_F.jpg";
            
            try {
                // Verificar se a foto existe no bucket 'funcionais'
                if (App\Helpers\StorageHelper::fotos()->exists($nomeArquivo)) {
                    $fotoUsuario = route('foto.usuario', $cpfLimpo);
                }
            } catch (\Exception $e) {
                $fotoUsuario = null;
            }
        }
    }
@endphp

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-light" style="background: #1a1a1a; border-bottom: 3px solid #bea55a;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color: #bac1c9;">
                <i class="fas fa-bars"></i>
            </a>
        </li>
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
        <li class="nav-item dropdown user-dropdown">
            <a class="nav-link user-toggle d-flex align-items-center" href="#" 
               id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               
                <div class="user-avatar mr-2">
                    @if($fotoUsuario)
                        <img src="{{ $fotoUsuario }}" alt="Foto do usuário" class="avatar-img">
                    @else
                        <div class="avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>
                
                <div class="user-info d-none d-lg-block">
                    <div class="user-name">{{ Auth::user() ? Auth::user()->name : '' }}</div>
                    <div class="user-role">Mat: {{ Auth::user() ? Auth::user()->matricula : '' }}</div>
                </div>
                
                <i class="fas fa-chevron-down ml-2 dropdown-arrow"></i>
            </a>
            
            <div class="dropdown-menu dropdown-menu-right user-dropdown-menu">
                <!-- Home -->
                <a class="dropdown-item" href="{{ route('public.home') }}">
                    <div class="item-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <span class="item-title">Início</span>
                </a>

                <div class="dropdown-divider"></div>

                <!-- Logout -->
                <a class="dropdown-item logout-item" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); if(confirm('Tem certeza que deseja sair do sistema?')) { document.getElementById('logout-form').submit(); }">
                    <div class="item-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="item-title">Sair</span>
                </a>
            </div>
        </li>
    </ul>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</nav>

<style>
:root {
    --primary-color: #bea55a;
    --primary-dark: #a69348;
    --primary-light: #d4b86a;
    --dark-bg: #1a1a1a;
    --text-light: #c1c5c9;
    --text-muted: #b0b6bb;
    --danger-color: #dc3545;
    --shadow-lg: 0 8px 25px rgba(0,0,0,0.2);
    --border-radius: 8px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* DROPDOWN */
.user-dropdown .user-toggle {
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    border: none;
    background: transparent;
    color: var(--text-light) !important;
}

.user-dropdown .user-toggle:focus {
    outline: none;
    box-shadow: none;
}

/* AVATAR */
.user-avatar {
    position: relative;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
    overflow: hidden;
}

.user-avatar .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    filter: contrast(1.1) brightness(1.05) saturate(1.1);
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    image-rendering: optimizeQuality;
    backface-visibility: hidden;
    transform: translateZ(0);
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1a1a1a;
    font-size: 1.2rem;
}

/* INFORMAÇÕES DO USUÁRIO */
.user-info {
    text-align: right;
    line-height: 1.2;
}

.user-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--text-light);
    margin-bottom: 2px;
}

.user-role {
    font-size: 0.75rem;
    color: var(--text-muted);
    opacity: 0.9;
}

/* Dropdown Arrow - Seta que gira */
.dropdown-arrow {
    font-size: 0.75rem;
    color: var(--text-muted);
    transition: var(--transition);
}

.user-toggle[aria-expanded="true"] .dropdown-arrow {
    transform: rotate(180deg);
    color: var(--primary-color);
}

/* MENU DROPDOWN */
.user-dropdown-menu {
    background: white;
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    padding: 0.5rem 0;
    margin-top: 0.5rem;
    min-width: 180px;
    max-width: 200px;
    animation: dropdownFadeIn 0.3s ease;
}

@keyframes dropdownFadeIn {
    from { 
        opacity: 0; 
        transform: translateY(-10px) scale(0.95); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0) scale(1); 
    }
}

/* ITENS DO DROPDOWN */
.dropdown-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border: none;
    background: transparent;
    color: #495057;
    text-decoration: none;
    position: relative;
}

.dropdown-item:focus {
    background: transparent;
    color: #495057;
    outline: none;
    text-decoration: none;
}

.item-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.item-icon i {
    font-size: 0.9rem;
    color: #6c757d;
}

.item-title {
    font-weight: 500;
    font-size: 0.875rem;
    line-height: 1.3;
}

/* LOGOUT ITEM */
.logout-item:focus {
    background: transparent;
    color: #495057;
}

/* DIVIDERS */
.dropdown-divider {
    margin: 0.5rem 0;
    border-color: #e9ecef;
}

/* RESPONSIVE */
@media (max-width: 991.98px) {
    .user-dropdown-menu {
        min-width: 160px;
        right: 0 !important;
        left: auto !important;
    }
}

/* NAVBAR efeitos de hover */
.navbar .nav-link:hover {
    color: #bea55a !important;
    transition: color 0.3s ease;
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