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
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
               id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
               style="color: #495057;">
               
                <div class="user-avatar mr-2 d-flex align-items-center justify-content-center rounded-circle" 
                     style="width: 42px; height: 42px; background: linear-gradient(45deg, #bea55a, #d4b86a); overflow: hidden;">
                    {{-- ✅ USAR VARIÁVEL CARREGADA ACIMA --}}
                    @if($fotoUsuario)
                        <img src="{{ $fotoUsuario }}" alt="Foto do usuário" 
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; image-rendering: optimizeQuality; filter: contrast(1.1) brightness(1.05) saturate(1.1); backface-visibility: hidden; transform: translateZ(0);">
                    @else
                        <i class="fas fa-user text-dark"></i>
                    @endif
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
                <a class="dropdown-item" href="{{ route('public.home') }}"
                   style="background-color: transparent; color: #495057;">
                    <i class="fas fa-home mr-2 text-primary"></i> Início
                </a>
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

/* Estilo para a foto do usuário */
.user-avatar img {
    transition: transform 0.2s ease;
    /* Otimizações para nitidez máxima */
    image-rendering: -webkit-optimize-contrast;
    image-rendering: -moz-crisp-edges;
    image-rendering: crisp-edges;
    image-rendering: pixelated;
    image-rendering: optimizeQuality;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
    /* Filtros para melhorar qualidade visual */
    filter: contrast(1.1) brightness(1.05) saturate(1.1);
    /* Anti-aliasing */
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.user-avatar:hover img {
    transform: scale(1.05) translateZ(0);
}
</style>