@extends('layouts.app')
@section('page-title')
  Editar Cadastro do Usuário
@endsection
@section('header-content')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">Editar Cadastro do Usuário</h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Editar Cadastro do Usuário</li>
      </ol>
    </div><!-- /.col -->
  </div>
@endsection
@section('content')
  <div class="row justify-content-center">
    <div class="col-md-11">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Editar Cadastro do Usuário:  "{{ $user->name }}"</h3>
        </div>
        <div class="card-body">
          <form action="{{ route('user.update', [ 'id' => $user->id ] ) }}" method="POST">
            @csrf
            @include('user.form-user')
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    $(document).ready(function() {
      $('#matriculation').mask("999999?9");
      $('#phone').mask("(99)99999999?9");
      $('#cpf').mask("999.999.999-99");
    });
  </script>
@endsection
