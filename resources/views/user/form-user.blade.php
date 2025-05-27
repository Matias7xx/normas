<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="name">Nome Completo</label>
      <input type="text" class="form-control" placeholder="Informe o nome completo do usuário" name="name" value="{{ old('name', $user->name) }}">
    </div>
  </div>
  <div class="col-md-2">
    <div class="form-group">
      <label for="matricula">Matrícula</label>
      <input type="text" class="form-control" maxlength="7" pattern="[0-9]{7}" inputmode="numeric" placeholder="Somente números" name="matricula" value="{{ old('matricula', $user->matricula) }}">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="text" class="form-control" placeholder="Informe o email do usuário" name="email" value="{{ old('email', $user->email) }}">
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="cpf">CPF</label>
      <input class="form-control" name="cpf" id="cpf" placeholder="CPF do representante" value="{{old('cpf', $user->cpf)}}"/>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="phone">Telefone</label>
      <input class="form-control" name="phone" id="phone" placeholder="Telefone do representante" value="{{old('phone', $user->telefone)}}"/>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="cargo_id">Cargo</label>
      <select name="cargo_id" class="form-control">
        <option value="">Selecione...</option>
            <option {{ (collect(old('cargo_id'))->contains($user->cargo_id)) ? 'selected':'' }} value="1">Delegado de Polícia</option>
            <option {{ (collect(old('cargo_id'))->contains($user->cargo_id)) ? 'selected':'' }} value="2">Investigador de Polícia</option>
            <option {{ (collect(old('cargo_id'))->contains($user->cargo_id)) ? 'selected':'' }} value="3">Escrivão de Polícia</option>
            <option {{ (collect(old('cargo_id'))->contains($user->cargo_id)) ? 'selected':'' }} value="4">Agente Operacional de Polícia</option>
      </select>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="role_id">Perfil</label>
      <select name="role_id" class="form-control">
        @if ($user->role)
          <option value="{{$user->role_id}}" selected>{{$user->role->name}}</option>
        @else
          <option value="">Selecione...</option>
        @endif
        @foreach ($roles as $key => $role)
          <option {{ (collect(old('role_id'))->contains($role->id)) ? 'selected':'' }} value="{{$role->id}}">{{$role->name}}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="password">Senha</label>
      <input type="password" class="form-control" placeholder="Informe uma senha inicial para o usuário" name="password">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label>Selecione um Status <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span></label>
      <select name="active" class="form-control">
        @if ($user->active)
          <option value="1">Ativo</option>
        @endif
        <option {{ old('active') == 1 ? 'selected':'' }} value="1">Ativo (default)</option>
        <option {{ old('active') == 0 ? 'selected':'' }} value="0">Inativo</option>
      </select>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-4">
    <div class="btn-group">
      <input class="btn btn-secondary" type="submit" value="Confirmar" onclick="return validateForm()">
      <a class="btn btn-default" href="{{ redirect()->getUrlGenerator()->previous() }}">Voltar</a>
    </div>
  </div>
</div>
