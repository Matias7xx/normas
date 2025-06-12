@extends('layouts.app')
@section('page-title')
	Perfis
@endsection
@section('header-content')
<div class="row mb-2">
  <div class="col-sm-6">
    <h1>Perfis</h1>
  </div>
  <div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
      <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
      <li class="breadcrumb-item active">Perfis</li>
    </ol>
  </div>
</div>
@endsection
@section('content')
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card card-dark">
        <div class="card-header">
          <h3>CRIAR UM NOVO PERFIL</h3>
        </div>
        <div class="card-body">
          <form class="form-horizontal" name="roleForm" method="post" action="{{ route('admin.addrole') }}">
            @csrf
            <div class="box-body">
              <div class="form-group">
                <label for="name">Perfil</label>
                <div class="col-sm-12">
                  <input type="text" class="form-control" name="name" placeholder="Perfil" required>
                </div>
              </div>
              <div class="form-group">
                <label for="description">Descrição</label>
                <div class="col-sm-12">
                  <textarea class="form-control" rows="3" name="description" placeholder="Descrição"></textarea>
                </div>
              </div>
              <div class="form-group">
                <label for="permissions">Permissões</label>
                <div class="col-sm-12">
                  <select class="form-control" name="permissions[]" id="permissions" multiple fomr="roleForm">
                    <option value="">Selecione...</option>
                    @foreach ($permissions as $p)
                      <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <a href="#" class="btn btn-default"><i class="fa fa-angle-double-left"></i> Voltar</a>
              <button type="submit" class="btn btn-secondary pull-right">Adicionar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card card-dark">
        <div class="card-header">
          <h3>PERFIS CADASTRADOS</h3>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-stripped table-hover">
            <thead>
              <tr>
                <th class="col-md-1">ID</th>
                <th class="col-md-2">Nome</th>
                <th class="col-md-4">Descrição</th>
                <th class="col-md-3">Permissões</th>
								<th class="col-md-2">Ações</th>
              </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                  <tr>
                    <td>
                      <strong>{{ $role->id }}</strong>
                      @if($role->id == 1)
                        <span class="badge badge-danger ml-1">ROOT</span>
                      @elseif($role->id == 2)
                        <span class="badge badge-warning ml-1">ADMIN</span>
                      @endif
                    </td>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->description }}</td>
                    <td>
                      @if($role->permissions->count() > 0)
                        @foreach ($role->permissions->take(2) as $p)
                          <small class="badge badge-info mr-1 mb-1">{{ $p->name }}</small>
                        @endforeach
                        @if($role->permissions->count() > 2)
                          <small class="badge badge-light">+{{ $role->permissions->count() - 2 }} mais</small>
                        @endif
                      @else
                        <span class="text-muted">Nenhuma</span>
                      @endif
                    </td>
										<td>
                      <div class="btn-group" role="group">
                        <!-- Botão Editar -->
											  <a href="{{ route('admin.editrole', ['id' => $role->id]) }}" 
                           class="btn btn-sm btn-warning" title="Editar">
                          <span class="fa fa-edit" aria-hidden="true"></span>
                        </a>
                        
                        <!-- Botão Excluir (apenas para perfis que não são críticos e se o usuário é root) -->
                        @if(Auth::user()->role_id == 1 && !in_array($role->id, [1, 2, 3]))
                          <button type="button" class="btn btn-sm btn-danger" 
                                  onclick="confirmDelete({{ $role->id }}, '{{ $role->name }}')" 
                                  title="Excluir">
                            <i class="fas fa-trash"></i>
                          </button>
                        @elseif(in_array($role->id, [1, 2, 3]))
                          <button type="button" class="btn btn-sm btn-secondary" 
                                  disabled title="Perfil protegido">
                            <i class="fas fa-lock"></i>
                          </button>
                        @endif
                      </div>
										</td>
                  </tr>
                @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmação de Exclusão -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteModalLabel">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Confirmar Exclusão
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
            <h5>Tem certeza que deseja excluir este perfil?</h5>
          </div>
          <div class="alert alert-warning">
            <strong>Atenção:</strong> Esta ação não pode ser desfeita. Certifique-se de que nenhum usuário está utilizando este perfil.
          </div>
          <div class="bg-light p-3 rounded">
            <strong>Perfil:</strong>
            <p class="mb-0 text-muted" id="roleNameDisplay" style="font-size: 14px;"></p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
          </button>
          <a href="#" id="confirmDeleteLink" class="btn btn-danger">
            <i class="fas fa-trash mr-1"></i> Confirmar Exclusão
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      $('#permissions').select2({
        placeholder: "SELECIONE AS PERMISSÕES PARA ESTE PERFIL",
        escapeMarkup: function (m) { return m; },
        language: {
          noResults: function () {
            return "NENHUM RESULTADO ENCONTRADO!";
          }
        }
      });
    });

    function confirmDelete(roleId, roleName) {
      $('#roleNameDisplay').text(roleName);
      $('#confirmDeleteLink').attr('href', '{{ url("/admin/delete-role") }}/' + roleId);
      $('#deleteModal').modal('show');
    }
  </script>
@endsection

@section('styles')
<style>
.btn-group .btn {
  margin-right: 2px;
}

.badge {
  font-size: 0.7rem;
}

.modal-content {
  border-radius: 10px;
  border: none;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.modal-header {
  border-radius: 10px 10px 0 0;
}

.alert {
  border-radius: 8px;
}

.btn:hover {
  transform: translateY(-1px);
  transition: all 0.2s ease;
}

/* Visualização das permissões */
.badge-info {
  background-color: #17a2b8;
  color: white;
}

.badge-light {
  background-color: #f8f9fa;
  color: #6c757d;
  border: 1px solid #dee2e6;
}
</style>
@endsection