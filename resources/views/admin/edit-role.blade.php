@extends('layouts.app')
@section('page-title')
  Editar Perfis
@endsection
@section('header-content')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">Editar Perfis</h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.listrole') }}">Perfis</a></li>
        <li class="breadcrumb-item active">Editar Perfis</li>
      </ol>
    </div><!-- /.col -->
  </div>
@endsection
@section('content')
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">EDITAR PERFIL</h3>
        </div>
        <div class="card-body">
          <form id="atualizarPerfil" name="atualizarPerfil" action="{{ route('admin.updaterole', ['id' => $role->id]) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row form-group">
              <div class="col-md-12">
                <label for="nome">NOME</label>
                <input type="text" class="form-control" name="name" id="name" value="{{$role->name}}">
              </div>
              <div class="col-md-12">
                <label for="description">DESCRIÇÃO</label>
                <textarea type="text" class="form-control" name="description" id="description">{{$role->description}}</textarea>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-md-12">
                <label for="permissions">PERMISSÕES</label>
                <select class="form-control select2" form="atualizarPerfil" multiple="multiple" name="permissions[]">
                  @foreach ($permissions as $key => $p)
                    <option {{$role->permissions->contains($p->id) ? 'selected' : ''}} value="{{$p->id}}">{{$p->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </form>
        </div>
        <div class="card-footer">
          <button form="atualizarPerfil" class="btn btn-primary">ATUALIZAR</button>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <!-- Scripts da página -->
  <script>
    $(function () {
      //Initialize Select2 Elements
      $('.select2').select2();
    });
  </script>
@endsection
