@extends('layouts.app')
@section('page-title')
	Permissões
@endsection
@section('header-content')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1>Permissões</h1>
    </div>
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Permissões</li>
      </ol>
    </div>
  </div>
@endsection
@section('content')
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card card-dark">
          <div class="card-header">
            <h3>CRIAR UMA NOVA PERMISSÃO</h3>
          </div>
          <div class="card-body">
            <form class="form-horizontal" name="roleForm" method="post" action="{{ route('admin.addpermission') }}">
              @csrf
              <div class="box-body">
                <div class="form-group">
                  <label for="name">Permissão</label>
                  <div class="col-md-12">
                    <input type="text" class="form-control" name="name" placeholder="Permissão" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="permissoes">Apelido</label>
                  <div class="col-md-12">
                    <input type="text" class="form-control" name="slug" placeholder="Apelido" required>
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
            <h3>PERMISSÕES EXISTENTES</h3>
          </div>
          <div class="card-body">
            <table class="table table-bordered table-stripped table-hover">
              <thead>
                <tr>
                  <th class="col-md-3">Nome</th>
                  <th class="col-md-6">Apelido</th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($permissions as $permission)
                    <tr>
                      <td>{{ $permission->name }}</td>
                      <td>{{ $permission->slug }}</td>
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

  </script>
@endsection
