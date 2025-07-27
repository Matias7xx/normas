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
                <label class="section-form-label">Tipo de norma <span class="text-danger">*</span></label>
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
            <div class="col-lg-4 col-md-6">
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

        <div class="row mt-3">
            <div class="col-lg-4 col-md-6">
                <label class="section-form-label">Status de vigência <span class="text-danger">*</span></label>
                <select class="section-form-select {{ $errors->has('vigente') ? 'border-error' : '' }}"
                    name="vigente" id="vigente" onchange="handleVigenciaChange()">
                    <option value="">Selecione...</option>
                    @foreach (\App\Models\Norma::getVigenteOptions() as $value => $label)
                        <option value="{{ $value }}" 
                            {{ old('vigente', $value == 'EM ANÁLISE' ? $value : '') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-3" id="vigencia_config_section" style="display: none;">
            <div class="col-12">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-cog mr-2"></i>Configurações de Vigência
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <!-- Checkbox para vigência indeterminada -->
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="vigencia_indeterminada" 
                                        name="vigencia_indeterminada" value="1" 
                                        {{ old('vigencia_indeterminada', old('vigencia_indeterminada_hidden', '1')) == '1' ? 'checked' : '' }}
                                        onchange="toggleDataLimite()">
                                    <label class="form-check-label" for="vigencia_indeterminada" id="vigencia_indeterminada_label">
                                        <strong id="vigencia_indeterminada_text">Vigência por tempo indeterminado</strong>
                                        <br><small class="text-muted" id="vigencia_indeterminada_help">Marque esta opção se a norma não tem data limite definida</small>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Campo de data limite -->
                            <div class="col-md-6" id="data_limite_container">
                                <label class="section-form-label">Data limite para mudança de vigência</label>
                                <input type="date" class="section-form-input {{ $errors->has('data_limite_vigencia') ? 'border-error' : '' }}"
                                    name="data_limite_vigencia" id="data_limite_vigencia" 
                                    value="{{ old('data_limite_vigencia') }}"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                <small class="form-text text-muted" id="data_limite_help">
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
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
                <div class="custom-file">
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

/* FORMULÁRIOS */
.section-form-label {
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 8px;
    display: block;
    position: relative;
    z-index: 10;
    height: 24px;
    line-height: 24px;
    min-height: 24px;
    margin-left: -20px;
}

.section-form-input, .section-form-select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
    background-color: white;
    height: 46px;
    box-sizing: border-box;
    display: flex;
    align-items: center;
}

input[type="date"].section-form-input {
    height: 46px;
    padding: 8px 12px;
    line-height: 1.5;
    -webkit-appearance: none;
    -moz-appearance: textfield;
}

input[type="date"].section-form-input::-webkit-calendar-picker-indicator {
    position: absolute;
    right: 12px;
    width: 20px;
    height: 20px;
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
    line-height: 1.2;
}

#resumo-contador {
    font-weight: 600;
    color: var(--info-color);
}

/* ===== UPLOAD DE ARQUIVO ===== */
.col-lg-8, .col-lg-4 {
    display: flex;
    flex-direction: column;
}

.row .col-lg-8,
.row .col-lg-4 {
    padding-top: 0;
    padding-bottom: 0;
}

.custom-file {
    position: relative;
    display: block;
    width: 100%;
    height: 46px;
    margin-bottom: 0;
    margin-top: 0;
}

.custom-file-input {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 46px;
    margin: 0;
    opacity: 0;
}

.custom-file-label {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1;
    height: 46px;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    transition: all 0.3s ease;
    background-color: white;
    display: flex;
    align-items: center;
    margin-bottom: 0;
    box-sizing: border-box;
    font-size: 14px;
    font-weight: 400;
    color: #495057;
    overflow: hidden;
}

.custom-file-label::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 3;
    display: block;
    height: 42px;
    padding: 10px 12px;
    line-height: 1.5;
    color: #495057;
    content: "Browse";
    background-color: #e9ecef;
    border-left: 2px solid #e0e0e0;
    border-radius: 0 4px 4px 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    box-sizing: border-box;
}

.row {
    display: flex;
    align-items: flex-start;
}

.row .col-lg-8,
.row .col-lg-4 {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.section-form-input,
.section-form-select,
.custom-file {
    vertical-align: top;
    margin-top: 0;
}

.custom-file-input:focus ~ .custom-file-label {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(64, 64, 64, 0.25);
}

.custom-file-input:focus ~ .custom-file-label::after {
    border-left-color: var(--primary-color);
    background-color: rgba(64, 64, 64, 0.1);
}

.custom-file-label:hover {
    border-color: #b0b0b0;
}

.custom-file-label:hover::after {
    background-color: #dee2e6;
    border-left-color: #b0b0b0;
}

.custom-file-input.border-error ~ .custom-file-label {
    border-color: var(--danger-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.custom-file-input.border-error ~ .custom-file-label::after {
    border-left-color: var(--danger-color) !important;
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.custom-file + .form-text,
.custom-file + small {
    margin-top: 8px !important;
    display: block;
    clear: both;
    z-index: 10;
    position: relative;
}

.section-form-label + .custom-file {
    margin-top: 0;
}

.col-lg-4 .section-form-label {
    margin-bottom: 8px;
}

.col-lg-8 .section-form-label {
    margin-bottom: 8px;
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
    
    .section-form-label {
        height: 22px;
        line-height: 22px;
        margin-bottom: 6px;
    }
    
    .section-form-input, 
    .section-form-select,
    .custom-file,
    .custom-file-input,
    .custom-file-label {
        height: 42px;
    }
    
    .custom-file-label {
        padding: 8px 10px;
        font-size: 13px;
    }
    
    .custom-file-label::after {
        height: 38px;
        padding: 8px 10px;
        font-size: 13px;
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

    handleVigenciaChange();
    toggleDataLimite();
});

// Função para controlar a exibição da seção de configurações de vigência
function handleVigenciaChange() {
    const vigenciaSelect = document.getElementById('vigente');
    const configSection = document.getElementById('vigencia_config_section');
    const vigenciaValue = vigenciaSelect.value;
    
    // Mostrar configurações apenas para VIGENTE ou NÃO VIGENTE
    if (vigenciaValue === 'VIGENTE' || vigenciaValue === 'NÃO VIGENTE') {
        configSection.style.display = 'block';
        updateCheckboxText();
        updateHelpText();
        aplicarEstadoInicial();
    } else {
        // Para EM ANÁLISE ou valores vazios, esconder a seção
        configSection.style.display = 'none';
        
        // Limpar e resetar campos quando a seção não está visível
        const checkbox = document.getElementById('vigencia_indeterminada');
        const dataInput = document.getElementById('data_limite_vigencia');
        const dataContainer = document.getElementById('data_limite_container');
        
        // Não resetar se há valores old() (após erro de validação)
        const hasOldValues = "{{ old('vigencia_indeterminada_hidden') }}" !== "";
        
        if (!hasOldValues) {
            // Resetar checkbox para marcado (padrão) apenas se não há valores old()
            checkbox.checked = true;
            
            // Limpar e desabilitar campo de data
            dataInput.value = '';
            dataInput.disabled = true;
            dataContainer.classList.add('disabled');
        }
        
        // Limpar texto de ajuda
        const helpText = document.getElementById('data_limite_help');
        if (helpText) {
            helpText.innerHTML = '';
        }
    }
}

// Função para aplicar estado inicial baseado nos valores old()
function aplicarEstadoInicial() {
    const checkbox = document.getElementById('vigencia_indeterminada');
    const dataInput = document.getElementById('data_limite_vigencia');
    
    // Verificar se há valores old() (indicando retorno após erro de validação)
    const oldCheckboxValue = "{{ old('vigencia_indeterminada_hidden', '') }}";
    const oldDataValue = "{{ old('data_limite_vigencia', '') }}";
    
    if (oldCheckboxValue !== "") {
        // Há valor old(), aplicar o estado salvo
        checkbox.checked = oldCheckboxValue === '1';
    } else {
        // Não há valor old(), aplicar padrão (marcado)
        checkbox.checked = true;
    }
    
    // Aplicar estado do campo de data baseado no checkbox
    toggleDataLimite();
}

// Função para atualizar o texto do checkbox baseado no status de vigência
function updateCheckboxText() {
    const vigenciaSelect = document.getElementById('vigente');
    const checkboxText = document.getElementById('vigencia_indeterminada_text');
    const checkboxHelp = document.getElementById('vigencia_indeterminada_help');
    const vigenciaValue = vigenciaSelect.value;
    
    if (vigenciaValue === 'VIGENTE') {
        checkboxText.textContent = 'Vigente por tempo indeterminado';
        checkboxHelp.textContent = 'Marque esta opção se a norma permanecerá vigente indefinidamente';
    } else if (vigenciaValue === 'NÃO VIGENTE') {
        checkboxText.textContent = 'Não Vigente por tempo indeterminado';
        checkboxHelp.textContent = 'Marque esta opção se a norma permanecerá não vigente indefinidamente';
    } else {
        checkboxText.textContent = 'Vigência por tempo indeterminado';
        checkboxHelp.textContent = 'Marque esta opção se a norma não tem data limite definida';
    }
}

// Função para controlar o campo de data limite
function toggleDataLimite() {
    const checkbox = document.getElementById('vigencia_indeterminada');
    const dataContainer = document.getElementById('data_limite_container');
    const dataInput = document.getElementById('data_limite_vigencia');
    
    if (checkbox.checked) {
        // Vigência indeterminada = desabilitar data limite
        dataContainer.classList.add('disabled');
        dataInput.disabled = true;
        // Só limpar se não há valor old() (evita perder dados após erro de validação)
        const oldDataValue = "{{ old('data_limite_vigencia', '') }}";
        if (oldDataValue === "") {
            dataInput.value = '';
        }
    } else {
        // Vigência determinada = habilitar data limite
        dataContainer.classList.remove('disabled');
        dataInput.disabled = false;
    }
    
    updateHelpText();
}

// Função para atualizar o texto de ajuda
function updateHelpText() {
    const vigenciaSelect = document.getElementById('vigente');
    const checkbox = document.getElementById('vigencia_indeterminada');
    const helpText = document.getElementById('data_limite_help');
    const vigenciaValue = vigenciaSelect.value;
    
    if (!vigenciaValue || vigenciaValue === 'EM ANÁLISE') {
        helpText.innerHTML = '';
        return;
    }
    
    if (checkbox.checked) {
        helpText.innerHTML = '<i class="fas fa-info-circle text-info mr-1"></i>A norma permanecerá com este status indefinidamente.';
    } else {
        if (vigenciaValue === 'VIGENTE') {
            helpText.innerHTML = '<i class="fas fa-calendar-times text-success mr-1"></i>Nesta data, a norma será alterada para <strong>NÃO VIGENTE</strong>.';
        } else if (vigenciaValue === 'NÃO VIGENTE') {
            helpText.innerHTML = '<i class="fas fa-calendar-check text-success mr-1"></i>Nesta data, a norma será alterada para <strong>VIGENTE</strong>.';
        }
    }
}

// Função para garantir que o valor do checkbox seja sempre enviado
function garantirEnvioCheckbox() {
    const form = document.querySelector('form');
    const checkbox = document.getElementById('vigencia_indeterminada');
    
    // Remover qualquer campo hidden anterior
    const hiddenExistente = document.querySelector('input[name="vigencia_indeterminada_hidden"]');
    if (hiddenExistente) {
        hiddenExistente.remove();
    }
    
    // Criar campo hidden que sempre será enviado
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'vigencia_indeterminada_hidden';
    hiddenInput.value = checkbox.checked ? '1' : '0';
    
    // Inserir o campo hidden logo após o checkbox
    checkbox.parentNode.insertBefore(hiddenInput, checkbox.nextSibling);
}

// Função para interceptar o submit do formulário
function interceptFormSubmit() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(event) {
        const vigenciaSelect = document.getElementById('vigente');
        const vigenciaValue = vigenciaSelect.value;
        const checkbox = document.getElementById('vigencia_indeterminada');
        const dataInput = document.getElementById('data_limite_vigencia');
        
        // Se a seção de vigência não está visível, não processar
        if (vigenciaValue !== 'VIGENTE' && vigenciaValue !== 'NÃO VIGENTE') {
            // Remover name dos campos para não serem enviados
            checkbox.removeAttribute('name');
            dataInput.removeAttribute('name');
            
            // Remover qualquer campo hidden
            const hiddenExistente = document.querySelector('input[name="vigencia_indeterminada_hidden"]');
            if (hiddenExistente) {
                hiddenExistente.remove();
            }
            
            return; // Permitir submit normal
        }
        
        // Se a data está desabilitada, limpar o valor
        if (dataInput.disabled) {
            dataInput.value = '';
        }
        
        // Remover o atributo name do checkbox original para evitar envio duplicado
        checkbox.removeAttribute('name');
        
        // Garantir que o campo hidden tenha o valor correto
        garantirEnvioCheckbox();
    });
}

// Atualizar o campo hidden sempre que o checkbox mudar
function setupCheckboxListener() {
    const checkbox = document.getElementById('vigencia_indeterminada');
    
    checkbox.addEventListener('change', function() {
        toggleDataLimite();
        garantirEnvioCheckbox(); // Atualizar campo hidden
    });
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    handleVigenciaChange();
    toggleDataLimite();
    garantirEnvioCheckbox(); // Criar campo hidden inicial
    setupCheckboxListener();
    interceptFormSubmit();
    
    // Adicionar listener para mudança no select de vigência
    const vigenciaSelect = document.getElementById('vigente');
    vigenciaSelect.addEventListener('change', handleVigenciaChange);
});
</script>