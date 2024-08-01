<div class="row">
    <div class="col-md-2">
        <label class="section-form-label">Data</label>
        <input type="date" class="section-form-input {{ $errors->has('data') ? 'border-error' : '' }}"
            name="data" id="data" value="{{ old('data') }}">
    </div>
    <div class="col-md-2">
        <label class="section-form-label">Publicidade</label>
        <select class="section-form-select {{ $errors->has('publicidade') ? 'border-error' : '' }}"
            name="publicidade" id="publicidade">
            <option value=""></option>
                @foreach ($publicidades as $publicidade)
                    <option value="{{ $publicidade->id }}">{{ mb_strtoupper($publicidade->publicidade) }}</option>
                @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="section-form-label">Tipos de normas</label>
        <select class="section-form-select {{ $errors->has('tipo') ? 'border-error' : '' }}"
            name="tipo" id="tipo">
            <option value=""></option>
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo->id }}">{{ mb_strtoupper($tipo->tipo) }}</option>
                @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="section-form-label">Órgãos</label>
        <select class="section-form-select {{ $errors->has('orgao') ? 'border-error' : '' }}"
            name="orgao" id="orgao">
            <option value=""></option>
                @foreach ($orgaos as $orgao)
                    <option value="{{ $orgao->id }}">{{ mb_strtoupper($orgao->orgao) }}</option>
                @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <label class="section-form-label">Descrição</label>
        <input type="text" class="section-form-input {{ $errors->has('descricao') ? 'border-error' : '' }}"
            name="descricao" id="descricao" value="{{ old('descricao') }}">
    </div>
    <div class="col-md-4">
        <label class="section-form-label">Anexo</label>
        <input type="file" class="section-form-input {{ $errors->has('anexo') ? 'border-error' : '' }}"
            name="anexo" id="anexo" value="{{ old('anexo') }}" accept=".pdf">
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <label class="section-form-label">Resumo da norma</label>
        <input type="text" class="section-form-input {{ $errors->has('resumo') ? 'border-error' : '' }}"
            name="resumo" id="resumo" value="{{ old('resumo') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="btn-group">
            <button class="btn btn-success" type="submit" onclick="return validateForm()"><i class="fas fa-save"></i> Confirmar</button>
            &nbsp;&nbsp;<a class="btn btn-secondary" href="{{route('normas.norma_list')}}"><i class="fas fa-arrow-left"></i> Voltar para a lista</a>
        </div>
    </div>
</div>
