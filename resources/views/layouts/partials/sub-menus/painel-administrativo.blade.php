<ul class="nav nav-treeview">
  @can ('administrador')
  {{-- @if(Auth::user()->role_id == 1 || Auth::user()->role->name == 'root') Necessário para usar sem API --}}
    <li class="nav-item">
      <a href="{{ route('user.create') }}" class="nav-link {{ (Request::is('admin/create') ? 'active': '') }}">
        <i class="fa fa-user-plus nav-icon"></i>
        <p>Novo Usuário</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('user.index') }}" class="nav-link  {{ (Request::is('admin/users') ? 'active': '') }}">
        <i class="fa fa-users nav-icon"></i>
        <p>Usuários</p>
      </a>
    </li>

    <li class="nav-item">
      <a href="{{ route('admin.listrole') }}" class="nav-link  {{ (Request::is('admin/list-roles') ? 'active': '') }}">
        <i class="fa fa-suitcase nav-icon"></i>
        <p>Perfis</p>
      </a>
    </li>

    <li class="nav-item">
      <a href="{{ route('admin.listpermission') }}" class="nav-link  {{ (Request::is('admin/list-permissions') ? 'active': '') }}">
        <i class="fa fa-suitcase nav-icon"></i>
        <p>Permissões</p>
      </a>
    </li>
    {{-- @endif --}}
  @endcan
</ul>
