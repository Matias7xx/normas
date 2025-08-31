<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="name">Nome Completo <span class="text-danger">*</span></label>
      <input type="text" class="form-control @error('name') is-invalid @enderror" 
             placeholder="Informe o nome completo do usuário" 
             name="name" 
             value="{{ old('name', $user->name) }}" required>
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-2">
    <div class="form-group">
      <label for="matricula">Matrícula <span class="text-danger">*</span></label>
      <input type="text" class="form-control @error('matricula') is-invalid @enderror" 
             maxlength="8" 
             pattern="[0-9]{1,8}" 
             inputmode="numeric" 
             placeholder="Somente números" 
             name="matricula" 
             value="{{ old('matricula', $user->matricula) }}" required>
      @error('matricula')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" class="form-control @error('email') is-invalid @enderror" 
             placeholder="Informe o email do usuário" 
             name="email" 
             value="{{ old('email', $user->email) }}">
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="cpf">CPF</label>
      <input class="form-control @error('cpf') is-invalid @enderror" 
             name="cpf" 
             id="cpf" 
             placeholder="CPF do usuário" 
             value="{{ old('cpf', $user->cpf) }}"/>
      @error('cpf')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="phone">Telefone</label>
      <input class="form-control @error('telefone') is-invalid @enderror" 
             name="phone" 
             id="phone" 
             placeholder="Telefone do usuário" 
             value="{{ old('phone', $user->telefone) }}"/>
      @error('telefone')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="cargo_id">Cargo</label>
      <select name="cargo_id" class="form-control @error('cargo_id') is-invalid @enderror">
        <option value="">Selecione...</option>
        <option {{ old('cargo_id', $user->cargo_id) == '1' ? 'selected':'' }} value="1">Delegado de Polícia</option>
        <option {{ old('cargo_id', $user->cargo_id) == '2' ? 'selected':'' }} value="2">Investigador de Polícia</option>
        <option {{ old('cargo_id', $user->cargo_id) == '3' ? 'selected':'' }} value="3">Escrivão de Polícia</option>
        <option {{ old('cargo_id', $user->cargo_id) == '4' ? 'selected':'' }} value="4">Agente Operacional de Polícia</option>
      </select>
      @error('cargo_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="role_id">Perfil <span class="text-danger">*</span></label>
      <select name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
        @if ($user->role)
          <option value="{{ $user->role_id }}" selected>{{ $user->role->name }}</option>
        @else
          <option value="">Selecione...</option>
        @endif
        @foreach ($roles as $role)
          @if($role->id != ($user->role_id ?? null))
            <option {{ old('role_id') == $role->id ? 'selected':'' }} value="{{ $role->id }}">{{ $role->name }}</option>
          @endif
        @endforeach
      </select>
      @error('role_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="password">
        Senha 
        @if(!$user->exists)
          <span class="text-danger">*</span>
        @else
          <small class="text-muted">(deixe vazio para manter atual)</small>
        @endif
      </label>
      <input type="password" class="form-control @error('password') is-invalid @enderror" 
             placeholder="{{ $user->exists ? 'Nova senha (opcional)' : 'Informe uma senha inicial' }}" 
             name="password"
             {{ !$user->exists ? 'required' : '' }}>
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label>Status <span class="text-danger">*</span></label>
      <select name="active" class="form-control @error('active') is-invalid @enderror" required>
        <option {{ old('active', $user->active) == '1' ? 'selected':'' }} value="1">Ativo</option>
        <option {{ old('active', $user->active) == '0' ? 'selected':'' }} value="0">Inativo</option>
      </select>
      @error('active')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="form-group text-right">
      <button type="submit" class="btn btn-success">
        <i class="fas fa-save"></i> {{ $user->exists ? 'Atualizar' : 'Criar' }} Usuário
      </button>
      <a class="btn btn-secondary ml-2" href="{{ route('user.index') }}">
        <i class="fas fa-arrow-left"></i> Voltar
      </a>
    </div>
  </div>
</div>