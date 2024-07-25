<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('home') }}" class="brand-link">
      <img src="/images/brasao_pcpb.png" alt="Logo PCPB" width="30px;" class="brand-image" style="opacity: .8"/>
      <span class="brand-text font-weight-light">{{config('app.name')}}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            @if (isset($servidor->foto))
                <img src="https://sistemas.pc.pb.gov.br/media/foto/{{ $servidor->foto }}" class="img-circle elevation-2" alt="User Image">
            @else
                <img src="/images/avatar.svg" class="img-circle elevation-2" alt="User Image">
                {{--  {{ $servidor->foto }}  --}}
            @endif
        </div>
        <div class="info">
          <a href="#" class="d-block">{{ Auth::user() ? Auth::user()->matricula : '' }}</a>
          <a href="#" class="d-block">{{ Auth::user() ? Auth::user()->name : '' }}</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          @can ('administrador')
            <li class="nav-item has-treeview {{ (Request::is('admin*') ? 'menu-open': '') }}">
              <a href="#" class="nav-link {{ (Request::is('admin*') ? 'active': '') }}">
                <i class="nav-icon fa fa-tachometer-alt"></i>
                <p>
                  Painel Administrativo
                  <i class="right fa fa-angle-left"></i>
                </p>
              </a>
              @include('layouts.partials.sub-menus.painel-administrativo')
            </li>
          @endcan

            <li class="nav-item has-treeview {{ (Request::is('normas*') ? 'menu-open': '') }}">
                <a href="#" class="nav-link {{ (Request::is('normas*') ? 'active': '') }}">
                  <i class="nav-icon fas fa-layer-group"></i>
                  <p>
                    Normas
                    <i class="right fa fa-angle-left"></i>
                  </p>
                </a>
                @include('layouts.partials.sub-menus.menu-normas')
              </li>
          {{-- <li class="nav-item has-treeview {{ (Request::is('processes*') ? 'menu-open': '')  }}">
            <a href="#" class="nav-link {{ (Request::is('processes*') ? 'active': '') }}">
              <i class="nav-icon fa fa-project-diagram"></i>
              <p>
                Processos
                <i class="right fa fa-angle-left"></i>
              </p>
            </a>
            @include('layouts.partials.sub-menus.processes')
          </li> --}}

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
