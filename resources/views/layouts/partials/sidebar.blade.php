<!-- Main Sidebar Container -->
<aside class="main-sidebar elevation-4" style="background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);">

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex" style="border-bottom: 1px solid #404040;">
            <div class="image">
                @if (Auth::user()->cpf)
                    <img src="https://sistemas.pc.pb.gov.br/media/servidor/funcionais/{{ Auth::user()->cpf }}_SERVIDOR_F.jpg" 
                        class="img-circle elevation-2" alt="User Image"
                        style="border: 2px solid #bea55a;"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                @endif
                <div class="img-circle elevation-2 align-items-center justify-content-center"
                    style="width: 34px; height: 34px; background: linear-gradient(45deg, #bea55a, #d4b86a); border: 2px solid #bea55a; {{ Auth::user()->cpf ? 'display: none;' : 'display: flex;' }}">
                    <i class="fas fa-user text-dark"></i>
                </div>
            </div>
            <div class="info">
                <a href="#" class="d-block font-weight-bold" style="font-size: 0.9rem; color: #ffffff;">
                    {{ Auth::user() ? Auth::user()->matricula : '' }}
                </a>
                <a href="#" class="d-block" style="font-size: 0.8rem; color: #cccccc;">
                    {{ Auth::user() ? Str::limit(Auth::user()->name, 25) : '' }}
                </a>
            </div>
        </div>

        <!-- Sidebar Search Form -->
        <div class="form-inline px-3 mb-3">
            <div class="input-group w-100" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" 
                       type="search" placeholder="Buscar menu..." aria-label="Search"
                       style="border-color: #bea55a !important; background-color: #2c2c2c !important; color: #ffffff;">
                <div class="input-group-append">
                    <button class="btn text-white" style="background-color: #bea55a; border-color: #bea55a;">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                {{-- Administração --}}
                @if(Auth::user()->role_id == 1)
                <li class="nav-header text-uppercase" style="color: #bea55a; font-weight: bold; font-size: 0.75rem; letter-spacing: 1px;">
                    <i class="fas fa-cogs mr-2"></i> Administração
                </li>
                <li class="nav-item has-treeview {{ Request::is('admin*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('admin*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Painel Administrativo
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('user.create') }}" class="nav-link {{ Request::is('admin/create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Novo Usuário</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('user.index') }}" class="nav-link {{ Request::is('admin/users') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Usuários</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.listrole') }}" class="nav-link {{ Request::is('admin/list-roles') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Perfis</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.listpermission') }}" class="nav-link {{ Request::is('admin/list-permissions') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Permissões</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                {{-- Normas --}}
                <li class="nav-header text-uppercase" style="color: #bea55a; font-weight: bold; font-size: 0.75rem; letter-spacing: 1px; margin-top: 20px;">
                    <i class="fas fa-balance-scale mr-2"></i> Gestão de Normas
                </li>
                
                <li class="nav-item has-treeview {{ Request::is('normas*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('normas*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-layer-group" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Normas Jurídicas
                            <i class="right fas fa-angle-left"></i>
                            {{-- <span class="badge badge-info right">6232{{ \App\Models\Norma::where('status', true)->count() }}</span> --}}
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('normas.norma_list') }}" class="nav-link">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Pesquisar</p>
                            </a>
                        </li>
                        @can('gestor')
                        <li class="nav-item">
                            <a href="{{ route('normas.norma_create') }}" class="nav-link">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Cadastrar</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>

                {{-- Configurações --}}
                @can('gestor')
                <li class="nav-header text-uppercase" style="color: #bea55a; font-weight: bold; font-size: 0.75rem; letter-spacing: 1px; margin-top: 20px;">
                    <i class="fas fa-cog mr-2"></i> Configurações
                </li>
                
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tags" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Tipos de Normas
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('tipos.tipo_list') }}" class="nav-link">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Listar Tipos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tipos.tipo_create') }}" class="nav-link">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Cadastrar Tipo</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-building" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Órgãos
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('orgaos.orgao_list') }}" class="nav-link">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Listar Órgãos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('orgaos.orgao_create') }}" class="nav-link">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Cadastrar Órgão</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-key" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Palavras-chave
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('palavras_chaves.palavras_chaves_list') }}" class="nav-link">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Listar Palavras</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('palavras_chaves.palavras_chaves_create') }}" class="nav-link">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Cadastrar Palavra</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
            </ul>
        </nav>
    </div>
</aside>

<style>
/* Sidebar Styling */
.main-sidebar .nav-link {
    border-radius: 8px;
    margin: 2px 8px;
    transition: all 0.3s ease;
    background-color: transparent;
}

.main-sidebar .nav-link:hover {
    background-color: rgba(190, 165, 90, 0.1) !important;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(190, 165, 90, 0.2);
}

.main-sidebar .nav-link.active {
    background: rgba(190, 165, 90, 0.1) !important;
    color: #fff !important;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(190, 165, 90, 0.3);
}

.main-sidebar .nav-link.active p {
    color: #fff !important;
}

.main-sidebar .nav-link.active i {
    color: #fff !important;
}

.main-sidebar .nav-treeview .nav-link {
    padding-left: 3rem;
    font-size: 0.9rem;
    background-color: rgba(0, 0, 0, 0.1);
    margin: 1px 8px;
}

.main-sidebar .nav-treeview .nav-link:hover {
    background-color: rgba(190, 165, 90, 0.15) !important;
    border-left: 3px solid #bea55a;
    margin-left: 8px;
}

.main-sidebar .nav-header {
    padding: 1rem 1rem 0.5rem 1rem;
    margin-bottom: 0.5rem;
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 6px;
    margin: 0.5rem 8px;
}

/* Brand link hover effect */
.brand-link:hover {
    transform: scale(1.02);
    transition: transform 0.3s ease;
}

/* User panel enhancements */
.user-panel .info a:hover {
    color: #bea55a !important;
}

/* Badge styling */
.badge {
    font-size: 0.65rem;
    padding: 3px 3px;
}

.badge-info {
    background-color: #bea55a;
    color: #fff;
}

/* Scrollbar styling for sidebar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: #1a1a1a;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #bea55a;
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: #d4b86a;
}

/* Menu item text colors */
.main-sidebar .nav-link p {
    color: #ffffff;
}

.main-sidebar .nav-treeview .nav-link p {
    color: #cccccc;
}

/* Hover effect for text */
.main-sidebar .nav-link:hover p {
    color: #ffffff !important;
}

/* Active menu background for parent items */
.main-sidebar .nav-item.has-treeview.menu-open > .nav-link {
    background-color: rgba(190, 165, 90, 0.15) !important;
}

/* Search input styling */
.form-control-sidebar {
    background-color: #2c2c2c !important;
    border: 1px solid #404040 !important;
    color: #ffffff !important;
}

.form-control-sidebar:focus {
    border-color: #bea55a !important;
    box-shadow: 0 0 0 0.2rem rgba(190, 165, 90, 0.25) !important;
    background-color: #2c2c2c !important;
}
</style>