<!-- Seção de Informações Básicas -->
<div class="card card-outline card-dark mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-alt mr-2"></i>Informações Básicas da Norma
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-2 col-md-6">
                <label class="section-form-label">Data <span class="text-danger">*</span></label>
                <input type="date" class="section-form-input {{ $errors->has('data') ? 'border-error' : '' }}"
                    name="data" id="data" value="{{ old('data', date('Y-m-d')) }}">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="section-form-label">Publicidade <span class="text-danger">*</span></label>
                <select class="section-form-select {{ $errors->has('publicidade') ? 'border-error' : '' }}"
                    name="publicidade" id="publicidade">
                    <option value="">Selecione...</option>
                    @foreach ($publicidades as $publicidade)
                        <option value="{{ $publicidade->id }}" 
                            {{ old('publicidade', strtoupper($publicidade->publicidade) == 'PUBLICO' ? $publicidade->id : '') == $publicidade->id ? 'selected' : '' }}>
                            {{ mb_strtoupper($publicidade->publicidade) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="section-form-label">Vigência <span class="text-danger">*</span></label>
                <select class="section-form-select {{ $errors->has('vigente') ? 'border-error' : '' }}"
                    name="vigente" id="vigente">
                    <option value="">Selecione...</option>
                    @foreach (\App\Models\Norma::getVigenteOptions() as $value => $label)
                        <option value="{{ $value }}" 
                            {{ old('vigente', $value == 'EM ANÁLISE' ? $value : '') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 col-md-6">
                <label class="section-form-label">Tipos de norma <span class="text-danger">*</span></label>
                <select class="section-form-select {{ $errors->has('tipo') ? 'border-error' : '' }}"
                    name="tipo" id="tipo">
                    <option value="">Selecione...</option>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->id }}" 
                            {{ old('tipo', strtoupper($tipo->tipo) == 'DECRETO' ? $tipo->id : '') == $tipo->id ? 'selected' : '' }}>
                            {{ mb_strtoupper($tipo->tipo) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <label class="section-form-label">Órgão <span class="text-danger">*</span></label>
                <select class="section-form-select {{ $errors->has('orgao') ? 'border-error' : '' }}"
                    name="orgao" id="orgao">
                    <option value="">Selecione...</option>
                    @foreach ($orgaos as $orgao)
                        <option value="{{ $orgao->id }}" 
                            {{ old('orgao', stripos($orgao->orgao, 'polícia civil da paraíba') !== false ? $orgao->id : '') == $orgao->id ? 'selected' : '' }}>
                            {{ mb_strtoupper($orgao->orgao) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Seção de Conteúdo -->
<div class="card card-outline card-dark mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-edit mr-2"></i>Conteúdo da Norma
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <label class="section-form-label">Descrição <span class="text-danger">*</span></label>
                <input type="text" class="section-form-input {{ $errors->has('descricao') ? 'border-error' : '' }}"
                    name="descricao" id="descricao" value="{{ old('descricao') }}" 
                    placeholder="Informe a descrição da norma" maxlength="255">
                <small class="form-text text-muted">Máximo 255 caracteres</small>
            </div>
            <div class="col-lg-4 col-md-12">
                <label class="section-form-label">Anexo <span class="text-danger">*</span></label>
                <div class="custom-file mt-4">
                    <input type="file" class="custom-file-input {{ $errors->has('anexo') ? 'border-error' : '' }}"
                        name="anexo" id="anexo" accept=".pdf">
                    <label class="custom-file-label" for="anexo">Escolha o arquivo...</label>
                </div>
                <small class="form-text text-muted">
                    <i class="fas fa-file-pdf text-danger mr-1"></i>Somente arquivos PDF (máx. 20MB)
                </small>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <label class="section-form-label">Resumo da norma (ementa) <span class="text-danger">*</span></label>
                <textarea class="section-form-input {{ $errors->has('resumo') ? 'border-error' : '' }}"
                    name="resumo" id="resumo" rows="3" maxlength="1000"
                    placeholder="Informe um resumo do conteúdo da norma">{{ old('resumo') }}</textarea>
                <small class="form-text text-muted">
                    <span id="resumo-contador">0</span>/1000 caracteres
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Seção de Palavras-chave -->
<div class="card card-outline card-dark mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tags mr-2"></i>Palavras-chave
        </h3>
        <div class="card-tools">
            <span class="badge badge-warning">Obrigatório</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Seção Esquerda - Gerenciamento -->
            <div class="col-lg-7 col-md-12">
                <!-- Palavras-chave Existentes -->
                <div class="palavras-existentes-section">
                    <div class="section-header existing-header">
                        <i class="fas fa-search mr-2"></i>
                        <span>Palavras-chave já cadastradas</span>
                        <span class="badge badge-info ml-2">Existentes</span>
                    </div>
                    <div class="section-content">
                        <select class="select2-palavras-chave section-form-select" multiple="multiple" 
                            data-placeholder="🔍 Digite para buscar palavras-chave já cadastradas..."
                            style="width: 100%;" name="palavras_chave[]" id="palavras_chave_select">
                            @foreach ($palavras_chave as $palavra_chave)
                                <option value="{{ $palavra_chave->id }}"
                                    {{ in_array($palavra_chave->id, old('palavras_chave', [])) ? 'selected' : '' }}>
                                    {{ $palavra_chave->palavra_chave }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-info">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Selecione uma ou mais palavras-chave que já existem no sistema
                        </small>
                    </div>
                </div>

                <!-- Separador Visual -->
                <div class="section-divider">
                    <span class="divider-text">OU</span>
                </div>

                <!-- Adicionar Novas Palavras-chave -->
                <div class="palavras-novas-section">
                    <div class="section-header new-header">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span>Criar novas palavras-chave</span>
                        <span class="badge badge-success ml-2">Novo</span>
                    </div>
                    <div class="section-content">
                        <div class="input-group input-group-new">
                            <input type="text" class="section-form-input new-input" id="nova_palavra_chave" 
                                placeholder="✏️ Digite uma nova palavra-chave para criar..." maxlength="255">
                            <div class="input-group-append">
                                <button class="btn btn-success btn-add-new" type="button" id="btn_add_palavra_chave">
                                    <i class="fas fa-plus mr-1"></i>Adicionar
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-success">
                            <i class="fas fa-magic mr-1"></i>
                            Pressione Enter ou clique em "Adicionar" para adicionar uma nova palavra-chave
                        </small>
                    </div>
                </div>

                <!-- Container de Palavras-chave Adicionadas -->
                <div class="form-group">
                    <div id="palavras_chave_container" class="palavras-container">
                        <div class="empty-state">
                            <i class="fas fa-tag text-muted"></i>
                            <span class="text-muted">Nenhuma palavra-chave nova adicionada</span>
                        </div>
                    </div>
                    <input type="hidden" id="novas_palavras_chave" name="novas_palavras_chave" 
                        value="{{ old('novas_palavras_chave') }}">
                </div>
            </div>

            <!-- Seção Direita - Instruções -->
            <div class="col-lg-5 col-md-12">
                <div class="info-panel">
                    <div class="info-header">
                        <h5><i class="fas fa-info-circle mr-2"></i>Como funciona</h5>
                    </div>
                    <div class="info-body">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="info-content">
                                <strong>Palavras-chave existentes:</strong>
                                <p>Busque e selecione palavras-chave já cadastradas no sistema. Ao salvar a norma, a palavra será a ela vinculada.</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="info-content">
                                <strong>Novas palavras-chave:</strong>
                                <p>Adicione novas palavras-chave. Elas serão criadas e vinculadas automaticamente à norma ao salvar.</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                            </div>
                            <div class="info-content">
                                <strong>Importante:</strong>
                                <p>As palavras-chave facilitam a busca e categorização das normas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seção de Ações -->
<div class="card card-outline card-secondary">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="btn-group">
                    <button class="btn btn-dark btn-lg" type="submit" onclick="return validateForm()">
                        <i class="fas fa-save mr-2"></i>Salvar Norma
                    </button>
                    <a class="btn btn-secondary btn-lg ml-2" href="{{route('normas.norma_list')}}">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar para a lista
                    </a>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Campos marcados com <span class="text-danger">*</span> são obrigatórios
                </small>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== CORES E TEMA ===== */
:root {
    --primary-color: #404040;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
}

/* ===== CARDS E SEÇÕES ===== */
.card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.card-header {
    background: linear-gradient(135deg, var(--primary-color), #5a5a5a);
    color: white;
    border-radius: 8px 8px 0 0 !important;
    padding: 12px 20px;
}

.card-title {
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
}

.card-tools .badge {
    font-size: 0.75rem;
    padding: 4px 8px;
}

/* ===== FORMULÁRIOS ===== */
.section-form-label {
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 8px;
    display: block;
}

.section-form-input, .section-form-select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
    background-color: white;
}

.section-form-input:focus, .section-form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(64, 64, 64, 0.25);
    outline: none;
}

.border-error {
    border-color: var(--danger-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* ===== TEXTAREA COM CONTADOR ===== */
#resumo {
    resize: vertical;
    min-height: 80px;
}

#resumo-contador {
    font-weight: 600;
    color: var(--info-color);
}

/* ===== UPLOAD DE ARQUIVO ===== */
.custom-file-label {
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.custom-file-input:focus ~ .custom-file-label {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(64, 64, 64, 0.25);
}

/* ===== SEÇÕES DE PALAVRAS-CHAVE ===== */
.palavras-existentes-section, .palavras-novas-section {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.palavras-existentes-section:hover, .palavras-novas-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.section-header {
    padding: 12px 20px;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
}

.existing-header {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.new-header {
    background: linear-gradient(135deg, #868e96, #727b84);
    color: white;
}

.section-content {
    padding: 20px;
}

/* ===== SEPARADOR VISUAL ===== */
.section-divider {
    text-align: center;
    margin: 25px 0;
    position: relative;
}

.section-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(to right, transparent, #dee2e6, transparent);
}

.divider-text {
    background: white;
    padding: 8px 20px;
    color: var(--secondary-color);
    font-weight: 600;
    border: 2px solid #dee2e6;
    border-radius: 20px;
    position: relative;
    z-index: 1;
}

/* ===== INPUTS ESPECÍFICOS ===== */
.new-input {
    border-left: 0 !important;
    border-right: 0 !important;
}

.new-input-icon {
    background: linear-gradient(135deg, #868e96, #727b84);
    color: white;
    border: 2px solid #868e96;
    border-right: 0;
}

.btn-add-new {
    background: linear-gradient(135deg, #868e96, #727b84);
    border: 2px solid #868e96;
    border-left: 0;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-add-new:hover {
    background: linear-gradient(135deg, #727b84, #868e96);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(134, 142, 150, 0.3);
}

.input-group-new {
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.input-group-new .section-form-input {
    border: 2px solid #868e96;
}

.input-group-new .section-form-input:focus {
    border-color: #868e96;
    box-shadow: 0 0 0 0.2rem rgba(134, 142, 150, 0.25);
}

/* ===== SELECT2 PARA EXISTENTES ===== */
.palavras-existentes-section .select2-container--default .select2-selection--multiple {
    border: 2px solid #6c757d;
}

.palavras-existentes-section .select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #6c757d;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
}

.palavras-existentes-section .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    border: none;
}

/* ===== BADGES NOS HEADERS ===== */
.badge-info {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 11px;
    padding: 4px 8px;
}

.badge-success {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 11px;
    padding: 4px 8px;
}

/* ===== TEXTOS DE AJUDA COLORIDOS ===== */
.text-info {
    color: #6c757d !important;
    font-weight: 500;
}

.text-success {
    color: #868e96 !important;
    font-weight: 500;
}

/* ===== CONTAINER DE PALAVRAS-CHAVE ===== */
.palavras-container {
    min-height: 80px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.palavras-container:hover {
    border-color: var(--primary-color);
    background: linear-gradient(135deg, #f1f3f4, #e2e6ea);
}

.empty-state {
    text-align: center;
    padding: 20px;
    color: var(--secondary-color);
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 8px;
    display: block;
}

.palavra-chave-tag {
    display: inline-block;
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    padding: 8px 15px;
    margin: 4px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.palavra-chave-tag:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.palavra-chave-tag .remover-palavra-chave {
    margin-left: 8px;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.palavra-chave-tag .remover-palavra-chave:hover {
    opacity: 1;
    color: #ffebee;
}

/* ===== PAINEL DE INFORMAÇÕES ===== */
.info-panel {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    height: fit-content;
}

.info-header {
    background: linear-gradient(135deg, var(--primary-color), #5a5a5a);
    color: white;
    padding: 12px 20px;
}

.info-header h5 {
    margin: 0;
    font-weight: 600;
}

.info-body {
    padding: 20px;
}

.info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-icon {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    flex-shrink: 0;
}

.info-content {
    flex: 1;
}

.info-content strong {
    color: var(--primary-color);
    display: block;
    margin-bottom: 4px;
}

.info-content p {
    margin: 0;
    color: var(--secondary-color);
    font-size: 14px;
    line-height: 1.4;
}

/* ===== BOTÕES ===== */
.btn-dark {
    background: linear-gradient(135deg, var(--primary-color), #5a5a5a);
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-dark:hover {
    background: linear-gradient(135deg, #5a5a5a, var(--primary-color));
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.btn-secondary {
    background: linear-gradient(135deg, var(--secondary-color), #868e96);
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #868e96, var(--secondary-color));
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* ===== RESPONSIVIDADE ===== */
@media (max-width: 768px) {
    .card-body {
        padding: 15px;
    }
    
    .btn-lg {
        padding: 8px 16px;
        font-size: 14px;
    }
    
    .info-panel {
        margin-top: 20px;
    }
    
    .section-form-input, .section-form-select {
        padding: 8px 10px;
    }
}

/* ===== ANIMAÇÕES ===== */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.palavra-chave-tag {
    animation: fadeIn 0.3s ease;
}

/* ===== UTILITÁRIOS ===== */
.text-danger {
    color: var(--danger-color) !important;
}

.text-muted {
    color: var(--secondary-color) !important;
}

.form-text {
    font-size: 12px;
    margin-top: 4px;
}
</style>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se o jQuery está carregado
    if (typeof $ === 'undefined') {
        console.error('jQuery não está carregado!');
        return;
    }
    
    $(function() {
        // Inicializa array para armazenar novas palavras-chave
        let novasPalavrasChave = [];
        
        // *** RECUPERAR PALAVRAS-CHAVE DO OLD() ***
        const oldNovasPalavrasChave = $('#novas_palavras_chave').val();
        if (oldNovasPalavrasChave) {
            try {
                novasPalavrasChave = JSON.parse(oldNovasPalavrasChave);
                // Recriar as tags visuais das palavras-chave salvas
                restaurarPalavrasChave();
            } catch (e) {
                console.error('Erro ao recuperar palavras-chave:', e);
            }
        }
        
        // Função para restaurar palavras-chave após erro de validação
        function restaurarPalavrasChave() {
            if (novasPalavrasChave.length > 0) {
                $("#palavras_chave_container .empty-state").remove();
                
                novasPalavrasChave.forEach(function(palavra) {
                    var tagHtml = `
                        <div class="palavra-chave-tag" data-palavra="${palavra}">
                            <i class="fas fa-tag mr-2"></i>${palavra}
                            <a href="javascript:void(0)" class="text-white ml-2 remover-palavra-chave" data-palavra="${palavra}">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    `;
                    $("#palavras_chave_container").append(tagHtml);
                });
            }
        }
        
        // Contador de caracteres para o resumo
        $('#resumo').on('input', function() {
            const count = $(this).val().length;
            $('#resumo-contador').text(count);
            
            if (count > 800) {
                $('#resumo-contador').addClass('text-warning');
            } else if (count > 950) {
                $('#resumo-contador').addClass('text-danger').removeClass('text-warning');
            } else {
                $('#resumo-contador').removeClass('text-warning text-danger').addClass('text-info');
            }
        });
        
        // Atualiza label do arquivo
        $('#anexo').on('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'Escolha o arquivo...';
            $(this).next('.custom-file-label').text(fileName);
        });
        
        // Configuração do Select2 para palavras-chave
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

            function formatPalavraChave(palavra) {
                if (!palavra.id) {
                    return palavra.text;
                }
                
                var $palavra = $(
                    '<span><i class="fas fa-tag mr-2"></i> ' + palavra.text + '</span>'
                );
                
                return $palavra;
            }

            function formatPalavraChaveSelection(palavra) {
                return palavra.text;
            }
        }
        
        // Função para atualizar o estado vazio
        function atualizarEstadoVazio() {
            const container = $("#palavras_chave_container");
            const emptyState = container.find('.empty-state');
            
            if (novasPalavrasChave.length === 0) {
                if (emptyState.length === 0) {
                    container.append(`
                        <div class="empty-state">
                            <i class="fas fa-tag text-muted"></i>
                            <span class="text-muted">Nenhuma palavra-chave nova adicionada</span>
                        </div>
                    `);
                }
            } else {
                emptyState.remove();
            }
        }
        
        // Função para adicionar palavra-chave à lista
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
            
            // Verificar se a palavra-chave já foi adicionada
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
            
            // Adicionar à lista
            novasPalavrasChave.push(palavra_chave);
            
            // Atualizar campo oculto
            $("#novas_palavras_chave").val(JSON.stringify(novasPalavrasChave));
            
            // Remover estado vazio
            $("#palavras_chave_container .empty-state").remove();
            
            // Adicionar elemento visual
            var tagHtml = `
                <div class="palavra-chave-tag" data-palavra="${palavra_chave}">
                    <i class="fas fa-tag mr-2"></i>${palavra_chave}
                    <a href="javascript:void(0)" class="text-white ml-2 remover-palavra-chave" data-palavra="${palavra_chave}">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            `;
            
            $("#palavras_chave_container").append(tagHtml);
            
            // Limpar campo
            $("#nova_palavra_chave").val('').focus();
        }
        
        // Botão para adicionar nova palavra-chave
        $("#btn_add_palavra_chave").on("click", function(e) {
            e.preventDefault();
            adicionarPalavraChave();
        });
        
        // Permitir uso da tecla Enter para adicionar palavra-chave
        $("#nova_palavra_chave").on("keypress", function(e) {
            if (e.which == 13) {
                e.preventDefault();
                adicionarPalavraChave();
            }
        });
        
        // Remover palavra-chave da lista
        $(document).on('click', '.remover-palavra-chave', function() {
            var palavra = $(this).data('palavra');
            
            // Remover do array
            novasPalavrasChave = novasPalavrasChave.filter(item => item !== palavra);
            
            // Atualizar campo oculto
            $("#novas_palavras_chave").val(JSON.stringify(novasPalavrasChave));
            
            // Remover elemento visual
            $(this).parent().remove();
            
            // Atualizar estado vazio
            atualizarEstadoVazio();
        });
        
        // Sobrescrever o validateForm para incluir validação de palavras-chave
        window.validateFormOriginal = window.validateForm || function() { return true; };
        
        window.validateForm = function() {
            // Validações originais
            if (!window.validateFormOriginal()) {
                return false;
            }
            
            // Verificar se campo vigente foi preenchido
            if (!$("#vigente").val()) {
                $(document).Toasts('create', {
                    title: "Atenção!",
                    class: 'bg-danger',
                    autohide: true,
                    delay: 5000,
                    position: 'topRight',
                    body: "O campo 'Status de Vigência' é obrigatório."
                });
                $("#vigente").focus();
                return false;
            }
            
            // Verificar se há pelo menos uma palavra-chave (existente ou nova)
            const palavrasExistentes = $("#palavras_chave_select").val() || [];
            const novasPalavras = novasPalavrasChave || [];
            
            if (palavrasExistentes.length === 0 && novasPalavras.length === 0) {
                $(document).Toasts('create', {
                    title: "Atenção!",
                    class: 'bg-danger',
                    autohide: true,
                    delay: 5000,
                    position: 'topRight',
                    body: "É obrigatório selecionar pelo menos uma palavra-chave existente ou criar uma nova."
                });
                $("#palavras_chave_select").focus();
                return false;
            }
            
            // Verificar se há palavras-chave curtas demais
            if (novasPalavrasChave.length > 0) {
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
        
        // Inicializar contador do resumo
        $('#resumo').trigger('input');
        
        // Inicializar estado vazio
        atualizarEstadoVazio();
    });
});
</script>