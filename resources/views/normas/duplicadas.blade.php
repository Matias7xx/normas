@extends('layouts.app')

@section('page-title')
    Normas Duplicadas
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">
                <i class="fas fa-copy mr-2"></i>
                Normas Duplicadas/Idênticas
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('normas.norma_list') }}">Normas</a></li>
                <li class="breadcrumb-item active">Duplicadas</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Informações sobre a busca -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-panel">
                <div class="info-header">
                    <h5>
                        <i class="fas fa-info-circle mr-2"></i>
                        Detecção de Normas Duplicadas
                    </h5>
                </div>
                <div class="info-content">
                    <p>
                        Normas que são <strong>idênticas ou quase idênticas</strong>, 
                        considerando o mesmo <strong>tipo, órgão e data</strong> com conteúdo muito similar. 
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if(isset($duplicadas) && count($duplicadas) > 0)
        <div class="alert-section alert-warning">
            <div class="alert-header">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Encontrados <strong>{{ count($duplicadas) }}</strong> grupos de normas idênticas ou quase idênticas.
            </div>
        </div>

        @foreach($duplicadas as $grupo_index => $grupo)
            <div class="group-card">
                <div class="group-header">
                    <h5>
                        <i class="fas fa-layer-group mr-2"></i>
                        Grupo {{ $grupo_index + 1 }} - {{ count($grupo) }}
                    </h5>
                </div>
                <div class="group-content">
                    <div class="row">
                        @foreach($grupo as $norma)
                            <div class="col-md-6 mb-3">
                                <div class="norma-card border-warning">
                                    <div class="norma-header">
                                        <div class="norma-id">ID: {{ $norma->id }}</div>
                                        <div class="norma-status">
                                            <span class="status-badge {{ $norma->vigente == 'VIGENTE' ? 'status-vigente' : ($norma->vigente == 'NÃO VIGENTE' ? 'status-nao-vigente' : 'status-analise') }}">
                                                {{ $norma->vigente }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="norma-content">
                                        <h6 class="norma-title">{{ $norma->descricao }}</h6>
                                        <div class="norma-details">
                                            <div class="detail-item">
                                                <span class="detail-label">Data:</span>
                                                <span class="detail-value">{{ $norma->data ? $norma->data->format('d/m/Y') : 'N/A' }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Tipo:</span>
                                                <span class="detail-value">{{ $norma->tipo->tipo ?? 'N/A' }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Órgão:</span>
                                                <span class="detail-value">{{ $norma->orgao->orgao ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="norma-actions">
                                            <a href="{{ route('normas.view', $norma->id) }}" 
                                               class="action-btn"
                                               target="_blank">
                                                <i class="fas fa-eye"></i> Ver PDF
                                            </a>
                                            <a href="{{ route('normas.norma_edit', $norma->id) }}" 
                                               class="action-btn">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <button type="button" 
                                                    class="action-btn btn-danger" 
                                                    onclick="confirmarExclusao({{ $norma->id }}, '{{ $norma->descricao }}')">
                                                <i class="fas fa-trash"></i> Remover
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

    @else
        <div class="empty-state">
            <div class="empty-content">
                <i class="fas fa-check-circle"></i>
                <h4>Nenhuma norma duplicada encontrada!</h4>
                <p>
                    Não foram encontradas normas idênticas ou duplicatas reais no sistema.
                </p>
                <a href="{{ route('normas.norma_list') }}" class="btn-neutral">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar à Listagem
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-danger">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta norma?</p>
                <p class="modal-details" id="normaDetalhes"></p>
                <div class="modal-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Atenção:</strong> Esta ação não poderá ser desfeita.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-neutral" data-dismiss="modal">Cancelar</button>
                <form id="formExclusao" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <i class="fas fa-trash mr-1"></i>Confirmar Exclusão
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/*  CORES E VARIÁVEIS  */
:root {
    --primary-color: #2c3e50;
    --secondary-color: #6c757d;
    --neutral-color: #f8f9fa;
    --border-color: #e0e6ed;
    --text-color: #2c3e50;
    --text-muted: #6c757d;
    --danger-color: #e74c3c;
    --warning-color: #f39c12;
    --info-color: #3498db;
    --success-color: #27ae60;
    --shadow: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-hover: 0 4px 8px rgba(0,0,0,0.15);
    --border-radius: 8px;
    --spacing: 1rem;
}

/*  RESET E BASE  */
* {
    box-sizing: border-box;
}

.container-fluid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 15px;
}

/*  PAINEL DE INFORMAÇÕES  */
.info-panel {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.info-header {
    background: var(--secondary-color);
    color: white;
    padding: var(--spacing);
}

.info-header h5 {
    margin: 0;
    font-weight: 500;
}

.info-content {
    padding: var(--spacing);
}

.info-content p {
    margin-bottom: 0;
    color: var(--text-color);
}

/*  BOTÕES  */
.btn-primary {
    background: var(--primary-color);
    color: white;
    border: 1px solid var(--primary-color);
    padding: 0.6rem 1.2rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-primary:hover {
    background: #34495e;
    border-color: #34495e;
    transform: translateY(-1px);
    box-shadow: var(--shadow-hover);
    color: white;
    text-decoration: none;
}

.btn-neutral {
    background: white;
    color: var(--text-color);
    border: 1px solid var(--border-color);
    padding: 0.6rem 1.2rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-neutral:hover {
    background: var(--neutral-color);
    border-color: var(--secondary-color);
    transform: translateY(-1px);
    box-shadow: var(--shadow-hover);
    color: var(--text-color);
    text-decoration: none;
}

.btn-danger {
    background: var(--danger-color);
    color: white;
    border: 1px solid var(--danger-color);
    padding: 0.6rem 1.2rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-danger:hover {
    background: #c0392b;
    border-color: #c0392b;
    transform: translateY(-1px);
    box-shadow: var(--shadow-hover);
    color: white;
    text-decoration: none;
}

/*  ALERTAS  */
.alert-section {
    padding: var(--spacing);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing);
    border-left: 4px solid;
}

.alert-section.alert-warning {
    background: #fef3e2;
    border-left-color: var(--warning-color);
    color: #8b5a00;
}

.alert-header {
    font-size: 0.95rem;
}

/*  CARDS DE GRUPOS  */
.group-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
    overflow: hidden;
}

.group-header {
    background: var(--warning-color);
    color: white;
    padding: var(--spacing);
    border-bottom: 1px solid var(--border-color);
}

.group-header h5 {
    margin: 0;
    font-weight: 500;
    font-size: 1.1rem;
}

.group-content {
    padding: var(--spacing);
}

/*  CARDS DE NORMAS  */
.norma-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
    overflow: hidden;
}

.norma-card.border-warning {
    border-left: 4px solid var(--warning-color);
}

.norma-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.norma-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem var(--spacing);
    background: var(--neutral-color);
    border-bottom: 1px solid var(--border-color);
}

.norma-id {
    font-weight: 600;
    color: var(--text-color);
}

.norma-content {
    padding: var(--spacing);
}

.norma-title {
    margin: 0 0 0.75rem 0;
    color: var(--text-color);
    font-weight: 500;
    font-size: 1rem;
    line-height: 1.4;
}

.norma-details {
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.detail-label {
    font-weight: 500;
    color: var(--text-muted);
    min-width: 80px;
    flex-shrink: 0;
}

.detail-value {
    color: var(--text-color);
}

/*  STATUS BADGES  */
.status-badge {
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-vigente {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-nao-vigente {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-analise {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

/*  AÇÕES  */
.norma-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-btn {
    padding: 0.4rem 0.8rem;
    background: white;
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}

.action-btn:hover {
    background: var(--neutral-color);
    border-color: var(--secondary-color);
    color: var(--text-color);
    text-decoration: none;
    transform: translateY(-1px);
}

.action-btn.btn-danger {
    background: var(--danger-color);
    color: white;
    border-color: var(--danger-color);
}

.action-btn.btn-danger:hover {
    background: #c0392b;
    border-color: #c0392b;
    color: white;
}

/*  ESTADO VAZIO  */
.empty-state {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.empty-content {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-content i {
    font-size: 3rem;
    color: var(--success-color);
    margin-bottom: 1rem;
    display: block;
}

.empty-content h4 {
    color: var(--text-color);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.empty-content p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

/*  MODAIS  */
.modal-content {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
    background: var(--primary-color);
    color: white;
    border-bottom: none;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    padding: 1rem 1.5rem;
}

.modal-header-danger {
    background: var(--danger-color);
}

.modal-title {
    font-weight: 500;
    margin: 0;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
    text-shadow: none;
}

.modal-header .close:hover {
    color: white;
    opacity: 1;
}

.modal-body {
    padding: 1.5rem;
    color: var(--text-color);
}

.modal-details {
    color: var(--text-muted);
    font-style: italic;
}

.modal-warning {
    background: #fff3cd;
    padding: 0.75rem;
    border-radius: 4px;
    border-left: 4px solid var(--warning-color);
    color: #856404;
    font-size: 0.9rem;
    margin-top: 1rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    background: var(--neutral-color);
    border-radius: 0 0 var(--border-radius) var(--border-radius);
}

/*  RESPONSIVIDADE  */
@media (max-width: 768px) {
    .norma-header {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .norma-actions {
        justify-content: center;
    }
    
    .detail-item {
        flex-direction: column;
        margin-bottom: 0.75rem;
    }
    
    .detail-label {
        min-width: auto;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
}

@media (max-width: 576px) {
    .action-btn {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
}

/*  ANIMAÇÕES  */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.group-card,
.norma-card {
    animation: fadeIn 0.3s ease;
}

/*  UTILITÁRIOS  */
.mb-4 { margin-bottom: 2rem; }
.mb-3 { margin-bottom: 1.5rem; }
.mb-2 { margin-bottom: 1rem; }
</style>

<script>
function confirmarExclusao(normaId, descricao) {
    document.getElementById('normaDetalhes').textContent = `ID: ${normaId} - ${descricao}`;
    document.getElementById('formExclusao').action = `/normas/norma_destroy/${normaId}`;
    $('#modalExclusao').modal('show');
}
</script>

@endsection