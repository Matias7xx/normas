<div class="row">
    <div class="col-md-12">
        <label class="section-form-label">Descrição da palavra chave</label>
        <input type="text" class="section-form-input {{ $errors->has('palavra_chave') ? 'border-error' : '' }}"
            name="palavra_chave" id="palavra_chave" value="{{ $palavra_chave->palavra_chave }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="btn-group">
            <button class="btn btn-success" type="submit" onclick="return validateForm()"><i class="fas fa-save"></i>
                Confirmar</button>
            &nbsp;&nbsp;<a class="btn btn-secondary" href="{{ route('palavras_chaves.palavras_chaves_list') }}"><i
                    class="fas fa-arrow-left"></i> Voltar para a lista</a>
        </div>
    </div>
</div>
