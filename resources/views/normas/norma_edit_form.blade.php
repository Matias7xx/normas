@foreach ($norma->palavrasChave as $palavra_chave)
    @php
        $array_palavra_chave[] = $palavra_chave->id;
    @endphp
@endforeach
<input type="hidden" name="norma_id" value="{{ $norma->id }}">
<div class="row">
    <div class="col-md-2">
        <label class="section-form-label">Data</label>
        <input type="date" class="section-form-input {{ $errors->has('data') ? 'border-error' : '' }}"
            name="data" id="data" value="{{ $norma->data ? $norma->data->format('Y-m-d') : '' }}">
    </div>
    <div class="col-md-2">
        <label class="section-form-label">Publicidade</label>
        <select class="section-form-select {{ $errors->has('publicidade') ? 'border-error' : '' }}"
            name="publicidade" id="publicidade">
            <option value="">Selecione...</option>
            @foreach ($publicidades as $publicidade)
                <option value="{{ $publicidade->id }}" {{ $publicidade->id == $norma->publicidade->id ? 'selected' : '' }}>
                    {{ mb_strtoupper($publicidade->publicidade) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="section-form-label">Status de Vigência</label>
        <select class="section-form-select {{ $errors->has('vigente') ? 'border-error' : '' }}"
            name="vigente" id="vigente">
            <option value="">Selecione...</option>
            @foreach (\App\Models\Norma::getVigenteOptions() as $value => $label)
                <option value="{{ $value }}" {{ $value == $norma->vigente ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">
            Status atual: 
            <span class="badge {{ $norma->vigente_class }}">
                <i class="{{ $norma->vigente_icon }} mr-1"></i>{{ $norma->vigente }}
            </span>
        </small>
    </div>
    <div class="col-md-3">
        <label class="section-form-label">Tipos de normas</label>
        <select class="section-form-select {{ $errors->has('tipo') ? 'border-error' : '' }}" name="tipo"
            id="tipo">
            <option value="">Selecione...</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->id }}" {{ $tipo->id == $norma->tipo->id ? 'selected' : '' }}>
                    {{ mb_strtoupper($tipo->tipo) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="section-form-label">Órgãos</label>
        <select class="section-form-select {{ $errors->has('orgao') ? 'border-error' : '' }}" name="orgao"
            id="orgao">
            <option value="">Selecione...</option>
            @foreach ($orgaos as $orgao)
                <option value="{{ $orgao->id }}" {{ $orgao->id == $norma->orgao->id ? 'selected' : '' }}>
                    {{ mb_strtoupper($orgao->orgao) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <label class="section-form-label">Descrição</label>
        <input type="text" class="section-form-input {{ $errors->has('descricao') ? 'border-error' : '' }}"
            name="descricao" id="descricao" value="{{ $norma->descricao }}" placeholder="Informe a descrição da norma">
    </div>
    <div class="col-md-4">
        <label class="section-form-label">Substituir Anexo</label>
        <div class="input-group">
            <input type="file" class="section-form-input {{ $errors->has('anexo') ? 'border-error' : '' }}"
                name="anexo" id="anexo" accept=".pdf">
            <div class="input-group-append">
            <a href="{{ asset('storage/normas/' . $norma->anexo) }}" 
                target="_blank" 
                class="btn btn-danger">
                <i class='fas fa-file-pdf'></i> Exibir Anexo
            </a>
            </div>
        </div>
        <small class="form-text text-muted">Somente arquivos PDF (máx. 10MB)</small>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <label class="section-form-label">Resumo da norma</label>
        <input type="text" class="section-form-input {{ $errors->has('resumo') ? 'border-error' : '' }}"
            name="resumo" id="resumo" value="{{ $norma->resumo }}" placeholder="Informe um resumo do conteúdo da norma">
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Gerenciamento de Palavras-chave</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="">Buscar palavras-chave existentes</label>
                            <select class="select2-palavras-chave section-form-select" multiple="multiple" 
                                data-placeholder="Digite para buscar palavras-chave existentes"
                                style="width: 100%;" name="add_palavra_chave[]">
                                @foreach ($palavra_chaves as $key => $palavra_chave_obj)
                                    @if (isset($array_palavra_chave) && !in_array($palavra_chave_obj->id, $array_palavra_chave))
                                        <option value="{{ $palavra_chave_obj->id }}">{{ $palavra_chave_obj->palavra_chave }}</option>
                                    @endif
                                    @if (!isset($array_palavra_chave))
                                        <option value="{{ $palavra_chave_obj->id }}">{{ $palavra_chave_obj->palavra_chave }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Digite para buscar palavras-chave já cadastradas</small>
                        </div>

                        <div class="form-group mt-4">
                            <label class="">Adicionar novas palavras-chave</label>
                            <div class="input-group">
                                <input type="text" class="section-form-input" id="nova_palavra_chave" 
                                    placeholder="Digite uma nova palavra-chave">
                                <div class="input-group-append">
                                    <button class="btn btn-dark" type="button" id="btn_add_palavra_chave">
                                        <i class="fas fa-plus"></i> Adicionar
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Pressione Enter para adicionar ou clique no botão</small>
                        </div>

                        <div class="form-group mt-4">
                            <label class="">Palavras-chave a serem adicionadas:</label>
                            <div id="palavras_chave_container" class="mt-2" 
                                style="min-height: 50px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border: 1px dashed #ccc;">
                                <!-- As tags de palavras-chave serão inseridas aqui -->
                            </div>
                            <!-- Campo oculto para armazenar as novas palavras-chave -->
                            <input type="hidden" id="novas_palavras_chave" name="novas_palavras_chave" value="">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-secondary h-100">
                            <div class="card-header" id="palavras_vinculadas" style="padding: 10px; bg-dark">
                                <h5 class="card-title" style="color: white;">Palavras-chave vinculadas</h5>
                            </div>
                            <div class="card-body" style="height: 350px; overflow-y: auto;">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Palavra-chave</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($norma->palavrasChave as $key => $palavra_chave)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $palavra_chave->palavra_chave }}</td>
                                                <td>
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        name="delete_palavra_chave" value="{{ $palavra_chave->id }}">
                                                        <i class="fas fa-trash"></i> Remover
                                                    </button>
                                                </td>
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
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <div class="btn-group">
            <button class="btn btn-dark btn-lg" type="submit" onclick="return validateForm()">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
            &nbsp;&nbsp;
            <a class="btn btn-secondary" href="{{ route('normas.norma_list') }}">
                <i class="fas fa-arrow-left"></i> Voltar para a lista
            </a>
        </div>
    </div>
</div>

<style>
    /*Estilos específicos para o formulário de normas*/
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #17a2b8;
        border-color: #404040;
        color: white;
        padding: 5px 10px;
        margin-top: 5px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }
    
    .palavra-chave-tag {
        display: inline-block;
        margin: 5px;
        padding: 8px 12px !important;
        border-radius: 4px;
    }
    
    .palavra-chave-tag .remover-palavra-chave {
        margin-left: 8px;
        opacity: 0.8;
    }
    
    .palavra-chave-tag .remover-palavra-chave:hover {
        opacity: 1;
    }
    
    /*Altura mínima do contêiner de palavras-chave se estiver vazio vazio */
    #palavras_chave_container {
        min-height: 60px;
    }
    
    /*Espaçamento geral*/
    .section-form-label {
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    /*Espaçamento botão Adicionar */
    #btn_add_palavra_chave {
        height: 100%;
    }
    
    /*Efeito de foco nos campos*/
    .section-form-input:focus, .section-form-select:focus {
        border-color: #404040;
        box-shadow: 0 0 0 0.1rem #404040;
    }
    
    /*Estilo para campos com erro */
    .border-error {
        border-color: #dc3545 !important;
    }

    .select2-container--default .select2-search--inline .select2-search__field {
        min-width: 200px;
        height: 38px;
        padding: 8px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #404040;
    }

    .select2-container--default .select2-results__option {
        padding: 8px 12px;
    }

    .select2-container--default .select2-selection--multiple {
        border-radius: 4px;
        border-color: #ced4da;
        min-height: 42px;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #404040;
        box-shadow: 0 0 0 0.1rem #404040;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #bea55a;
        color: white;
        padding: 5px 10px;
        margin-top: 5px;
        margin-right: 5px;
        border-radius: 4px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
        font-weight: bold;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #f8f9fa;
    }

    .select2-dropdown {
        border-color: #ced4da;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
    
    #palavras_vinculadas {
        background-color: #404040;
    }

    /* Estilos para o campo vigente */
    #vigente {
        /* border: 2px solid #404040; */
    }

    #vigente:focus {
        border-color: #404040;
        box-shadow: 0 0 0 0.1rem #404040;
    }

    /* Alert de status de vigência */
    .alert-info {
        border-left: 4px solid #404040;
    }
</style>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        //Verifica se o jQuery está carregado
        if (typeof $ === 'undefined') {
            console.error('jQuery não está carregado!');
            return;
        }
        
        $(function() {
            //Inicializa array para armazenar novas palavras-chave
            let novasPalavrasChave = [];
            
            //Configuração do Select2 para palavras-chave
            if ($.fn.select2) {
                $('.select2-palavras-chave').select2({
                    placeholder: "Digite para buscar palavras-chave existentes",
                    allowClear: true,
                    tags: false,
                    tokenSeparators: [','],
                    minimumInputLength: 1,
                    language: {
                        inputTooShort: function() {
                            return "Digite pelo menos 1 caractere para iniciar a busca";
                        },
                        searching: function() {
                            return "Buscando...";
                        },
                        noResults: function() {
                            return "Nenhuma palavra-chave encontrada";
                        }
                    },
                    templateResult: formatPalavraChave,
                    templateSelection: formatPalavraChaveSelection
                });

                //Função para formatar o resultado da busca
                function formatPalavraChave(palavra) {
                    if (!palavra.id) {
                        return palavra.text;
                    }
                    
                    var $palavra = $(
                        '<span><i class="fas fa-tag mr-2"></i> ' + palavra.text + '</span>'
                    );
                    
                    return $palavra;
                }

                //Função para formatar a palavra-chave selecionada
                function formatPalavraChaveSelection(palavra) {
                    return palavra.text;
                }
            }
            
            //Função para adicionar palavra-chave à lista
            function adicionarPalavraChave() {
                var palavra_chave = $("#nova_palavra_chave").val().trim();
                
                if (palavra_chave.length < 3) {
                    $(document).Toasts('create', {
                        title: "Atenção!",
                        class: 'bg-danger',
                        autohide: true,
                        delay: 3000,
                        position: 'topRight',
                        body: "A palavra-chave deve ter pelo menos 3 caracteres."
                    });
                    return;
                }
                
                //Verificar se a palavra-chave já foi adicionada
                if (novasPalavrasChave.includes(palavra_chave)) {
                    $(document).Toasts('create', {
                        title: "Atenção!",
                        class: 'bg-warning',
                        autohide: true,
                        delay: 3000,
                        position: 'topRight',
                        body: "Esta palavra-chave já foi adicionada à lista."
                    });
                    $("#nova_palavra_chave").val('');
                    return;
                }
                
                //Adicionar à lista
                novasPalavrasChave.push(palavra_chave);
                
                //Atualizar campo oculto
                $("#novas_palavras_chave").val(JSON.stringify(novasPalavrasChave));
                
                //Adicionar elemento visual
                var tagHtml = `
                    <div class="badge badge-info palavra-chave-tag mr-2 mb-2" style="font-size: 14px; padding: 8px;">
                        ${palavra_chave}
                        <a href="javascript:void(0)" class="text-white ml-2 remover-palavra-chave" data-palavra="${palavra_chave}">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                `;
                
                $("#palavras_chave_container").append(tagHtml);
                
                //Limpar campo
                $("#nova_palavra_chave").val('').focus();
            }
            
            //Botão para adicionar nova palavra-chave
            $("#btn_add_palavra_chave").on("click", function(e) {
                e.preventDefault(); // Impede o comportamento padrão
                adicionarPalavraChave();
            });
            
            //Permitir uso da tecla Enter para adicionar palavra-chave
            $("#nova_palavra_chave").on("keypress", function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    adicionarPalavraChave();
                }
            });
            
            //Remover palavra-chave da lista
            $(document).on('click', '.remover-palavra-chave', function() {
                var palavra = $(this).data('palavra');
                
                //Remover do array
                novasPalavrasChave = novasPalavrasChave.filter(item => item !== palavra);
                
                //Atualizar campo oculto
                $("#novas_palavras_chave").val(JSON.stringify(novasPalavrasChave));
                
                //Remover elemento visual
                $(this).parent().remove();
            });
            
            // Sobrescrever o validateForm para incluir validação de palavras-chave
            window.validateFormOriginal = window.validateForm || function() { return true; };
            
            window.validateForm = function() {
                // Validações originais
                if (!window.validateFormOriginal()) {
                    return false;
                }
                
                //Se houverem palavras-chave novas, verificar se o formulário pode ser enviado
                if (novasPalavrasChave.length > 0) {
                    //Verificar se há palavras-chave curtas demais
                    var palavrasCurtas = novasPalavrasChave.filter(palavra => palavra.length < 3);
                    if (palavrasCurtas.length > 0) {
                        $(document).Toasts('create', {
                            title: "Atenção!",
                            class: 'bg-danger',
                            autohide: true,
                            delay: 5000,
                            position: 'topRight',
                            body: "Existem palavras-chave com menos de 3 caracteres."
                        });
                        return false;
                    }
                }
                
                return true;
            };
        });
    });
</script>