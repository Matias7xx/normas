<!-- Main Sidebar Container -->
<aside class="main-sidebar elevation-4" style="background: #1a1a1a;">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex" style="border-bottom: 1px solid #404040;">
            <li class="nav-item d-flex align-items-center ml-3" style="width: 100%;">
                <img src="/images/brasao_pcpb.png" alt="Logo PCPB" width="35px" class="mr-2" style="opacity: .9; brightness(1.1) flex-shrink: 0;"/>
                <div style="min-width: 0; flex: 1;">
                    <h4 class="mb-0 font-weight-bold" style="color: #d8d8d8; font-size: 1.15rem; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        Biblioteca de Normas
                    </h4>
                    <small style="color: #6c757d; font-size: 1.0rem; line-height: 0.90; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        Polícia Civil da Paraíba
                    </small>
                </div>
            </li>
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
                <li class="nav-item has-treeview {{ Request::is('admin/users*') || Request::is('user/create') || Request::is('admin/list-*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('admin/users*') || Request::is('user/create') || Request::is('admin/list-*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Painel Administrativo
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('user.create') }}" class="nav-link {{ Request::is('user/create') ? 'active' : '' }}">
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
                
                <li class="nav-item has-treeview {{ Request::is('normas*') || Request::is('vigencia*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('normas*') || Request::is('vigencia*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-layer-group" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Normas Jurídicas
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('normas.norma_list') }}" class="nav-link {{ Request::is('normas') || Request::is('normas/norma_list') ? 'active' : '' }}">
                                <i class="fas fa-search nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Pesquisar</p>
                            </a>
                        </li>
                        {{-- Página para normas duplicadas --}}
                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                            <li class="nav-item">
                                <a href="{{ route('normas.duplicadas') }}" class="nav-link {{ Request::is('normas/duplicadas') ? 'active' : '' }}">
                                    &nbsp;&nbsp;&nbsp;<i class="fas fa-copy nav-icon" style="color: #bea55a;"></i>
                                    <p>Duplicadas</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('vigencia.dashboard') }}" class="nav-link {{ Request::is('vigencia/dashboard') ? 'active' : '' }}">
                                    &nbsp;&nbsp;&nbsp;<i class="fas fa-calendar-check nav-icon" style="color: #bea55a;"></i>
                                    <p style="color: #cccccc;">Vigências</p>
                                </a>
                            </li>
                        @endif

                        @can('gestor')
                        <li class="nav-item">
                            <a href="{{ route('normas.norma_create') }}" class="nav-link {{ Request::is('normas/create') || Request::is('normas/norma_create') ? 'active' : '' }}">
                                <i class="fas fa-plus nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Adicionar</p>
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
                
                <li class="nav-item has-treeview {{ Request::is('tipos*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('tipos*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tags" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Tipos de Normas
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('tipos.tipo_list') }}" class="nav-link {{ Request::is('tipos') || Request::is('tipos/tipo_list') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Listar Tipos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tipos.tipo_create') }}" class="nav-link {{ Request::is('tipos/create') || Request::is('tipos/tipo_create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Cadastrar Tipo</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ Request::is('orgaos*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('orgaos*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-building" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Órgãos
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('orgaos.orgao_list') }}" class="nav-link {{ Request::is('orgaos') || Request::is('orgaos/orgao_list') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Listar Órgãos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('orgaos.orgao_create') }}" class="nav-link {{ Request::is('orgaos/create') || Request::is('orgaos/orgao_create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Cadastrar Órgão</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ Request::is('palavras*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('palavras*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-key" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Palavras-chave
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('palavras_chaves.palavras_chaves_list') }}" class="nav-link {{ Request::is('palavras*list*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Listar Palavras</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('palavras_chaves.palavras_chaves_create') }}" class="nav-link {{ Request::is('palavras*create*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Cadastrar Palavra</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Área de Especificações --}}
                <li class="nav-item has-treeview {{ Request::is('especificacoes*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('especificacoes*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Especificações
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('especificacoes.especificacoes_list') }}" class="nav-link {{ Request::is('especificacoes*list*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Listar Especificações</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('especificacoes.especificacoes_create') }}" class="nav-link {{ Request::is('especificacoes*create*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Nova Especificação</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Gestão de Boletins - apenas para role 1 (root) ou 7 --}}
                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7)
                <li class="nav-header text-uppercase" style="color: #bea55a; font-weight: bold; font-size: 0.75rem; letter-spacing: 1px; margin-top: 20px;">
                    <i class="fas fa-newspaper mr-2"></i> Gestão de Boletins
                </li>
                
                <li class="nav-item has-treeview {{ Request::is('admin/boletins*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ Request::is('admin/boletins*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt" style="color: #bea55a;"></i>
                        <p style="color: #ffffff;">
                            Boletim Interno
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('boletins.index') }}" class="nav-link {{ Request::is('admin/boletins') ? 'active' : '' }}">
                                <i class="fas fa-list nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Listar</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('boletins.create') }}" class="nav-link {{ Request::is('admin/boletins/create') ? 'active' : '' }}">
                                <i class="fas fa-plus nav-icon" style="color: #bea55a;"></i>
                                <p style="color: #cccccc;">Adicionar</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

            </ul>
        </nav>
    </div>
</aside>

<style>
/* Sidebar Styling */
.main-sidebar .nav-link {
    border-radius: 2px 8px;
    transition: all 0.3s ease;
    background-color: transparent;
}

.main-sidebar .nav-link:hover {
    background-color: #c1a85a !important;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(190, 165, 90, 0.2);
}

.main-sidebar .nav-link.active {
    background: #c1a85a !important;
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
    background-color: #1a1a1a;
    margin: 1px 8px;
}

.main-sidebar .nav-treeview .nav-link:hover {
    background-color: #c1a85a !important;
    border-left: 3px solid black;
    margin-left: 8px;
}

.main-sidebar .nav-header {
    padding: 1rem 1rem 0.5rem 1rem;
    margin-bottom: 0.5rem;
    background-color: #1a1a1a;
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
    color: #c1a85a !important;
}

/* Badge styling */
.badge {
    font-size: 0.65rem;
    padding: 3px 3px;
}

.badge-info {
    background-color: #c1a85a;
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
    background: #c1a85a;
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: #c1a85a;
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

/* ÍCONES FICAM BRANCOS NO HOVER */
.main-sidebar .nav-link:hover i {
    color: #ffffff !important;
}

/* ÍCONES DOS SUBITENS FICAM BRANCOS NO HOVER */
.main-sidebar .nav-treeview .nav-link:hover i {
    color: #ffffff !important;
}

/* Active menu background for parent items */
.main-sidebar .nav-item.has-treeview.menu-open > .nav-link {
    background-color: #c1a85a !important;
}

.main-sidebar .nav-item.has-treeview.menu-open > .nav-link i {
    color: #ffffff !important;
}

/* Search input styling */
.form-control-sidebar {
    background-color: #1a1a1a !important;
    border: 1px solid #404040 !important;
    color: #ffffff !important;
}

.form-control-sidebar:focus {
    border-color: #c1a85a !important;
    box-shadow: 0 0 0 0.2rem rgba(190, 165, 90, 0.25) !important;
    background-color: #1a1a1a !important;
}
</style>