<ul class="nav nav-treeview">
    {{-- @can('add_roles') --}}


    @can('gestor')
        <li class="nav-item">
            <a href="{{ route('normas.norma_create') }}" class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
                &nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-danger"></i>
                <p>Cadastrar</p>
            </a>
        </li>
    @endcan
    <li class="nav-item">
        <a href="{{ route('normas.norma_list') }}" class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
            &nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-danger"></i>
            <p>Listar</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('normas.norma_search') }}" class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
            &nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-danger"></i>
            <p>Pesquisar</p>
        </a>
    </li>

    @can('gestor')
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="fas fa-cog nav-icon text-warning"></i>
                <p>Configurações</p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        &nbsp;&nbsp;&nbsp;<i class="fas fa-plus nav-icon text-warning"></i>
                        <p>Tipos</p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('tipos.tipo_list') }}"
                                class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-warning"></i>
                                <p>listar</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tipos.tipo_create') }}"
                                class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-warning"></i>
                                <p>Cadastrar</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        &nbsp;&nbsp;&nbsp;<i class="fas fa-plus nav-icon text-warning"></i>
                        <p>Órgãos</p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('orgaos.orgao_list') }}"
                                class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-warning"></i>
                                <p>listar</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('orgaos.orgao_create') }}"
                                class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-warning"></i>
                                <p>Cadastrar</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        &nbsp;&nbsp;&nbsp;<i class="fas fa-plus nav-icon text-warning"></i>
                        <p>Palavra Chave</p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('palavras_chaves.palavras_chaves_list') }}"
                                class="nav-link {{ Request::is('normas/norma_list') ? 'active' : '' }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-warning"></i>
                                <p>listar</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('palavras_chaves.palavras_chaves_create') }}"
                                class="nav-link {{ Request::is('normas/') ? 'active' : '' }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="far fa-dot-circle nav-icon text-warning"></i>
                                <p>Cadastrar</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    @endcan
    {{-- @endcan --}}
    {{-- <li class="nav-item">
    <a href="{{ route('process.show') }}" class="nav-link {{ (Request::is('processes') ? 'active': '') }}">
      <i class="fa fa-list-alt nav-icon"></i>
      <p>Listar</p>
    </a>
  </li>
  @can('root')
    <li class="nav-item">
      <a href="/processes/visitante-deletados" class="nav-link {{ (Request::is('processes/destroy') ? 'active': '') }}">
        <i class="fa fa-trash nav-icon"></i>
        <p>Deletados</p>
      </a>
    </li>
  @endcan --}}
</ul>
