<div class="row">
    <div class="col-md-12">
        <label class="section-form-label">Nome do Órgão</label>
        <input type="text" class="section-form-input {{ $errors->has('nome_orgao') ? 'border-error' : '' }}"
            name="nome_orgao" id="nome_orgao">
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="btn-group">
            <button class="btn btn-success" type="submit" onclick="return validateForm()"><i class="fas fa-save"></i> Confirmar</button>
            &nbsp;&nbsp;<a class="btn btn-secondary" href="{{route('orgaos.orgao_list')}}"><i class="fas fa-arrow-left"></i> Voltar para a lista</a>
        </div>
    </div>
</div>
