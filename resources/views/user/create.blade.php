@extends('layouts.app')
@section('page-title')
  Novo Usuário
@endsection
@section('header-content')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">Novo Usuário</h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Novo Usuário</li>
      </ol>
    </div><!-- /.col -->
  </div>
@endsection
@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card card-dark">
        <div class="card-header">
          <h3>CRIAR UM NOVO USUÁRIO</h3>
        </div>
        <div class="card-body">
          <form id="user_form" action="{{ route('user.store') }}" method="POST">
            @csrf
            @include('user.form-user')
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
  <script>
    $(document).ready(function() {
      $('#matriculation').mask("999999?9");
      $('#cnpj').mask("99.999.999/9999-99");
      $('#phone').mask("(99)99999999?9");
      $('#cpf').mask("999.999.999-99");
    });
  </script>
@endsection
