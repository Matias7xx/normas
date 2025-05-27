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
                <th class="col-md-5">Descrição</th>
                <th class="col-md-3">Permissões</th>
								<th class="col-md-1">Ações</th>
              </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                  <tr>
                    <td><strong>{{ $role->id }}</strong></td>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->description }}</td>
                    <td>
                      @foreach ($role->permissions as $p)
                        <div>{{ $p->name }}</div>
                      @endforeach
                    </td>
										<td>
											<a href="{{ route('admin.editrole', ['id' => $role->id]) }}" class="btn btn-warning"> <span class="fa fa-edit" aria-hidden="true"></span></a>
										</td>
                  </tr>
                @endforeach
            </tbody>
          </table>
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
  </script>
@endsection