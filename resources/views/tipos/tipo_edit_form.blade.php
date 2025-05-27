<input type="hidden" name="id_tipo" id="id_tipo" value="{{ $tipo->id }}">
<div class="row">
    <div class="col-md-12">
        <label class="section-form-label">Nome do Tipo de Norma </label>
        <input type="text" class="section-form-input {{ $errors->has('nome_tipo') ? 'border-error' : '' }}"
            name="nome_tipo" id="nome_tipo" value="{{ $tipo->tipo }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="btn-group">
            <button class="btn btn-dark" type="submit" onclick="return validateForm()"><i class="fas fa-save"></i> Confirmar</button>
            &nbsp;&nbsp;<a class="btn btn-secondary" href="{{route('tipos.tipo_list')}}"><i class="fas fa-arrow-left"></i> Voltar para a lista</a>
        </div>
    </div>
</div>
