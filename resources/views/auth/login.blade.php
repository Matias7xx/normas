@extends('layouts.guest')

@section('content')
<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-secondary">
      <div class="card-header text-center">
        <p class="mb-0">
          <a href="https://policiacivil.pb.gov.br" class="h1">
            <img height="80px" src="{{asset('images/brasao_pcpb_nome.png')}}"/>
          </a>
        </p>
        {{--  <p class="mt-0">{{env('APP_NAME')}}</p>  --}}
        <p class="mt-0">Biblioteca de Normas</p>
      </div>
      <div class="card-body">
        <p class="login-box-msg">Faça login para acessar o Sistema</p>

        <form action="{{route('login')}}" method="POST">
            @csrf
          <div class="input-group mb-3">
            <input type="text" name="matricula" class="form-control" placeholder="Matrícula">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-id-card"></span>
              </div>
            </div>
            @error('matricula')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
          </div>
          <div class="input-group mb-3">
            <input id="password" name="password" type="password" class="form-control" placeholder="Senha">
            <div id="hide-password" class="input-group-append" title="Mostrar senha">
              <div class="input-group-text">
                <span id="icon-password" class="fas fa-eye-slash"></span>
              </div>
            </div>
            <div id="show-password" class="input-group-append d-none" title="Ocultar senha">
              <div class="input-group-text">
                <span id="icon-password" class="fas fa-eye"></span>
              </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
          </div>
          <div class="row">
            <div class="col-8">
              <div class="icheck-secondary">
                <input type="checkbox" id="remember">
                <label for="remember">
                  LEMBRAR ME
                </label>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-secondary btn-block"><nobr><i class='fas fa-key'></i> ENTRAR</nobr></button>
            </div>
            <!-- /.col -->
          </div>
        </form>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <!-- /.login-box -->
@endsection

@section('scripts')
  <script>
    $('#hide-password').on('click', function() {
      $('#hide-password').addClass('d-none');
      $('#show-password').removeClass('d-none');
      $('#password').get(0).type = 'text';
    });
    $('#show-password').on('click', function() {
      $('#show-password').addClass('d-none');
      $('#hide-password').removeClass('d-none');
      $('#password').get(0).type = 'password';
    });
  </script>
@endsection
