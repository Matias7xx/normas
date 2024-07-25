@foreach ($norma->palavrasChave as $palavra_chave)
    @php
        $array_palavra_chave[] = $palavra_chave->id;
    @endphp
@endforeach
<input type="hidden" name="norma_id" value="{{ $norma->id }}">
<div class="row">
    <div class="col-8">
        <div class="row">
            <div class="col-md-3">
                <label class="section-form-label">Data</label>
                <input type="date" class="section-form-input {{ $errors->has('data') ? 'border-error' : '' }}"
                    name="data" id="data" value="{{ $norma->data }}">
            </div>
            <div class="col-md-3">
                <label class="section-form-label">Publicidade</label>
                <select class="section-form-select {{ $errors->has('publicidade') ? 'border-error' : '' }}"
                    name="publicidade" id="publicidade">
                    <option value="{{ $norma->publicidade->id }}">{{ mb_strtoupper($norma->publicidade->publicidade) }}
                    </option>
                    @foreach ($publicidades as $publicidade)
                        <option value="{{ $publicidade->id }}">{{ mb_strtoupper($publicidade->publicidade) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="section-form-label">Tipos de normas</label>
                <select class="section-form-select {{ $errors->has('tipo') ? 'border-error' : '' }}" name="tipo"
                    id="tipo">
                    <option value="{{ $norma->tipo->id }}">{{ mb_strtoupper($norma->tipo->tipo) }}</option>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->id }}">{{ mb_strtoupper($tipo->tipo) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label class="section-form-label">Órgãos</label>
                <select class="section-form-select {{ $errors->has('orgao') ? 'border-error' : '' }}" name="orgao"
                    id="orgao">
                    <option value="{{ $norma->orgao->id }}">{{ mb_strtoupper($norma->orgao->orgao) }}</option>
                    @foreach ($orgaos as $orgao)
                        <option value="{{ $orgao->id }}">{{ mb_strtoupper($orgao->orgao) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="section-form-label">Substituir Anexo</label>
                <input type="file" class="section-form-input {{ $errors->has('anexo') ? 'border-error' : '' }}"
                    name="anexo" id="anexo">
            </div>
            <div class="col-md-2">
                <br><a href='javascript:abrirPagina("../../storage/normas/{{ $norma->anexo }}",600,600);'><button
                        type="button" class='btn btn-danger'>
                        <nobr><i class='fas fa-file-pdf'></i> Exibir Anexo</nobr>
                    </button></a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label class="section-form-label">Descrição</label>
                <input type="text" class="section-form-input {{ $errors->has('descricao') ? 'border-error' : '' }}"
                    name="descricao" id="descricao" value="{{ $norma->descricao }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label class="section-form-label">Resumo da norma</label>
                <input type="text" class="section-form-input {{ $errors->has('resumo') ? 'border-error' : '' }}"
                    name="resumo" id="resumo" value="{{ $norma->resumo }}">
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Vincular palavras chave</label>
                    <select class="select2" multiple="multiple" data-placeholder="Selecione as palavras chave"
                        style="width: 100%;" name="add_palavra_chave[]">
                        @foreach ($palavra_chaves as $key => $palavra_chave_obj)
                            @if (isset($array_palavra_chave) && !in_array($palavra_chave_obj->id, $array_palavra_chave))
                                <option value="{{ $palavra_chave_obj->id }}">{{ $palavra_chave_obj->palavra_chave }}
                                </option>
                            @endif
                            @if (!isset($array_palavra_chave))
                                <option value="{{ $palavra_chave_obj->id }}">{{ $palavra_chave_obj->palavra_chave }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group">
                    <button class="btn btn-primary" type="submit" onclick="return validateForm()"><i
                            class="fas fa-save"></i> Editar Informações da Norma</button>
                    &nbsp;&nbsp; <button class="btn btn-warning" type="submit" onclick="return validateForm()"><i
                            class="fas fa-save" name="bt_vincular_palavra_chave" value="bt_vincular_palavra_chave"></i>
                        Vincular Palavras Chave</button>
                    &nbsp;&nbsp;<a class="btn btn-secondary" href="{{ route('normas.norma_list') }}"><i
                            class="fas fa-arrow-left"></i> Voltar para a lista</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header" style="padding: 5px; text-align: center;">
                            <h4>Palavras <b>CHAVE</b> Vinculadas</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Ord</th>
                                        <th>Id</th>
                                        <th>Texto</th>
                                        <th>**</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($norma->palavrasChave as $key => $palavra_chave)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $palavra_chave->id }}</td>
                                            <td>{{ $palavra_chave->palavra_chave }}</td>
                                            <td><button type="submit" class="btn btn-danger"
                                                    name="delete_palavra_chave" value="{{ $palavra_chave->id }}"><i
                                                        class="fas fa-trash"></i></button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
