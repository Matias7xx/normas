@extends('layouts.app')

@section('page-title')
    Controle de Vigência
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">
                <i class="fas fa-calendar-check mr-2"></i>
                Controle de Vigência das Normas
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('normas.norma_list') }}">Normas</a></li>
                <li class="breadcrumb-item active">Controle de Vigência</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Estatísticas Gerais -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stats-card stats-warning">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['vencendo_hoje'] ?? 0 }}</div>
                    <div class="stats-label">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Vencendo Hoje
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card stats-neutral">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['vencendo_7_dias'] ?? 0 }}</div>
                    <div class="stats-label">
                        <i class="fas fa-clock mr-1"></i>
                        Próximos 7 Dias
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card stats-danger">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['atrasadas'] ?? 0 }}</div>
                    <div class="stats-label">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Em Atraso
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Controles de Execução -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="control-panel">
                <div class="panel-header">
                    <h5>
                        <i class="fas fa-cogs mr-2"></i>
                        Atualizar Vigências
                    </h5>
                </div>
                <div class="panel-actions">
                    <button type="button" class="btn-primary" onclick="executarAtualizacao()">
                        <i class="fas fa-play mr-1"></i> Executar Atualização
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(isset($normasAtrasadas) && count($normasAtrasadas) > 0)
        <div class="alert-section alert-danger">
            <div class="alert-header">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Atenção!</strong> Existem <strong>{{ count($normasAtrasadas) }}</strong> normas que deveriam ter mudado de status automaticamente.
            </div>
        </div>

        <div class="section-card">
            <div class="section-header bg-danger">
                <h5>
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Normas em Atraso - {{ count($normasAtrasadas) }} normas
                </h5>
            </div>
            <div class="section-content">
                <div class="row">
                    @foreach($normasAtrasadas as $norma)
                        <div class="col-md-6 mb-3">
                            <div class="norma-card border-danger">
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
                                            <span class="detail-label">Data limite:</span>
                                            <span class="detail-value text-danger">{{ $norma->data_limite_vigencia ? $norma->data_limite_vigencia->format('d/m/Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Deveria mudar para:</span>
                                            <span class="next-status">{{ $norma->proximo_status }}</span>
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
                                                class="action-btn btn-update" 
                                                onclick="atualizarNormaEspecifica({{ $norma->id }}, '{{ $norma->descricao }}')">
                                            <i class="fas fa-sync"></i> Atualizar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(isset($normasParaHoje) && count($normasParaHoje) > 0)
        <div class="section-card">
            <div class="section-header bg-warning">
                <h5>
                    <i class="fas fa-clock mr-2"></i>
                    Vencendo Hoje - {{ count($normasParaHoje) }} normas
                </h5>
            </div>
            <div class="section-content">
                <div class="row">
                    @foreach($normasParaHoje as $norma)
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
                                            <span class="detail-label">Data limite:</span>
                                            <span class="detail-value text-warning">{{ $norma->data_limite_vigencia ? $norma->data_limite_vigencia->format('d/m/Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Mudará para:</span>
                                            <span class="next-status">{{ $norma->proximo_status }}</span>
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
                                                class="action-btn btn-update" 
                                                onclick="atualizarNormaEspecifica({{ $norma->id }}, '{{ $norma->descricao }}')">
                                            <i class="fas fa-sync"></i> Atualizar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(isset($normasProximos7Dias) && count($normasProximos7Dias) > 0)
        <div class="section-card">
            <div class="section-header bg-neutral">
                <h5>
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Próximos 7 Dias - {{ count($normasProximos7Dias) }} normas
                </h5>
            </div>
            <div class="section-content">
                <div class="row">
                    @foreach($normasProximos7Dias as $norma)
                        <div class="col-md-6 mb-3">
                            <div class="norma-card border-neutral">
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
                                            <span class="detail-label">Data limite:</span>
                                            <span class="detail-value">{{ $norma->data_limite_vigencia ? $norma->data_limite_vigencia->format('d/m/Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Mudará para:</span>
                                            <span class="next-status">{{ $norma->proximo_status }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Dias restantes:</span>
                                            <span class="days-remaining">{{ $norma->diasParaMudancaVigencia() }}</span>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(isset($normasProximos30Dias) && count($normasProximos30Dias) > 0)
        <div class="section-card">
            <div class="section-header bg-neutral">
                <h5>
                    <i class="fas fa-calendar-week mr-2"></i>
                    Próximos 30 Dias - {{ count($normasProximos30Dias) }} normas
                </h5>
            </div>
            <div class="section-content">
                <div class="table-responsive">
                    <table class="minimal-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descrição</th>
                                <th>Status Atual</th>
                                <th>Data Limite</th>
                                <th>Novo Status</th>
                                <th>Dias</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($normasProximos30Dias as $norma)
                                <tr>
                                    <td><strong>{{ $norma->id }}</strong></td>
                                    <td>{{ Str::limit($norma->descricao, 50) }}</td>
                                    <td>
                                        <span class="status-badge {{ $norma->vigente == 'VIGENTE' ? 'status-vigente' : ($norma->vigente == 'NÃO VIGENTE' ? 'status-nao-vigente' : 'status-analise') }}">
                                            {{ $norma->vigente }}
                                        </span>
                                    </td>
                                    <td>{{ $norma->data_limite_vigencia ? $norma->data_limite_vigencia->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="next-status">{{ $norma->proximo_status }}</span>
                                    </td>
                                    <td>
                                        <span class="days-remaining">{{ $norma->diasParaMudancaVigencia() }} dias</span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('normas.view', $norma->id) }}" 
                                               class="action-btn-sm" 
                                               target="_blank" 
                                               title="Ver PDF">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('normas.norma_edit', $norma->id) }}" 
                                               class="action-btn-sm" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if((!isset($normasAtrasadas) || count($normasAtrasadas) == 0) && 
        (!isset($normasParaHoje) || count($normasParaHoje) == 0) && 
        (!isset($normasProximos7Dias) || count($normasProximos7Dias) == 0) && 
        (!isset($normasProximos30Dias) || count($normasProximos30Dias) == 0))
        <div class="empty-state">
            <div class="empty-content">
                <i class="fas fa-check-circle"></i>
                <h4>Tudo em dia!</h4>
                <p>Não há normas com vigência programada para os próximos dias.</p>
                <a href="{{ route('normas.norma_list') }}" class="btn-neutral">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar à Listagem
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Modal de Confirmação para Atualização Individual -->
<div class="modal fade" id="modalAtualizacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Atualização de Vigência</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja atualizar o status de vigência desta norma?</p>
                <p class="modal-details" id="normaDetalhesAtualizacao"></p>
                <div class="modal-note">
                    <i class="fas fa-neutral-circle mr-1"></i>
                    <strong>Importante:</strong> Após a atualização, a vigência será marcada como indeterminada.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-neutral" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn-primary" id="btnConfirmarAtualizacao">
                    <i class="fas fa-sync mr-1"></i>Confirmar Atualização
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resultado da Execução -->
<div class="modal fade" id="modalResultado" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resultado da Execução</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="resultadoExecucao" class="execution-result"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-neutral" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
/* CORES E VARIÁVEIS  */
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

/* ===== RESET ===== */
* {
    box-sizing: border-box;
}

.container-fluid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 15px;
}

/*  CARDS DE ESTATÍSTICAS */
.stats-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing);
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--secondary-color);
    transition: all 0.3s ease;
}

.stats-card.stats-warning::before { background: var(--warning-color); }
.stats-card.stats-info::before { background: var(--info-color); }
.stats-card.stats-danger::before { background: var(--danger-color); }

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.stats-content {
    text-align: center;
    padding: 0.5rem 0;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 300;
    color: var(--text-color);
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 500;
}

.stats-label i {
    color: var(--secondary-color);
}

/* PAINEL DE CONTROLE */
.control-panel {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing);
}

.panel-header h5 {
    margin: 0;
    color: var(--text-color);
    font-weight: 500;
}

.panel-actions {
    display: flex;
    gap: 0.5rem;
}

/* BOTÕES  */
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

/* ALERTAS  */
.alert-section {
    padding: var(--spacing);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing);
    border-left: 4px solid;
}

.alert-section.alert-danger {
    background: #fef2f2;
    border-left-color: var(--danger-color);
    color: #991b1b;
}

.alert-header {
    font-size: 0.95rem;
}

/* SEÇÕES  */
.section-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
    overflow: hidden;
}

.section-header {
    padding: var(--spacing);
    border-bottom: 1px solid var(--border-color);
    color: white;
}

.section-header.bg-danger { background: var(--danger-color); }
.section-header.bg-warning { background: var(--warning-color); }
.section-header.bg-info { background: var(--info-color); }
.section-header.bg-neutral { background: var(--secondary-color); }

.section-header h5 {
    margin: 0;
    font-weight: 500;
    font-size: 1.1rem;
}

.section-content {
    padding: var(--spacing);
}

/* CARDS DE NORMAS */
.norma-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
    overflow: hidden;
}

.norma-card.border-danger { border-left: 4px solid var(--danger-color); }
.norma-card.border-warning { border-left: 4px solid var(--warning-color); }
.norma-card.border-info { border-left: 4px solid var(--info-color); }

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
    min-width: 120px;
    flex-shrink: 0;
}

.detail-value {
    color: var(--text-color);
}

.next-status {
    background: var(--info-color);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.days-remaining {
    background: var(--secondary-color);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

/* STATUS BADGES */
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

/* ===== AÇÕES ===== */
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

.action-btn.btn-update {
    background: var(--success-color);
    color: white;
    border-color: var(--success-color);
}

.action-btn.btn-update:hover {
    background: #219a52;
    border-color: #219a52;
    color: white;
}

.action-btn-sm {
    padding: 0.3rem 0.5rem;
    background: white;
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    margin: 0 0.1rem;
}

.action-btn-sm:hover {
    background: var(--neutral-color);
    border-color: var(--secondary-color);
    color: var(--text-color);
    text-decoration: none;
}

.table-actions {
    display: flex;
    justify-content: center;
    gap: 0.2rem;
}

/* TABELA */
.minimal-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.minimal-table th {
    background: var(--neutral-color);
    padding: 0.75rem 0.5rem;
    text-align: left;
    font-weight: 500;
    color: var(--text-color);
    border-bottom: 1px solid var(--border-color);
    font-size: 0.9rem;
}

.minimal-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid #f1f1f1;
    color: var(--text-color);
    font-size: 0.9rem;
    vertical-align: middle;
}

.minimal-table tbody tr {
    transition: all 0.3s ease;
}

.minimal-table tbody tr:hover {
    background: rgba(52, 73, 94, 0.03);
}

/* ESTADO VAZIO */
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

/* MODAIS */
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

.modal-note {
    background: #e3f2fd;
    padding: 0.75rem;
    border-radius: 4px;
    border-left: 4px solid var(--info-color);
    color: #1565c0;
    font-size: 0.9rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    background: var(--neutral-color);
    border-radius: 0 0 var(--border-radius) var(--border-radius);
}

.execution-result {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    max-height: 400px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    color: var(--text-color);
    border: 1px solid var(--border-color);
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* CORES DE TEXTO */
.text-danger { color: var(--danger-color) !important; }
.text-warning { color: var(--warning-color) !important; }
.text-info { color: var(--info-color) !important; }
.text-success { color: var(--success-color) !important; }

/* RESPONSIVIDADE */
@media (max-width: 768px) {
    .control-panel {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .panel-actions {
        justify-content: center;
    }
    
    .stats-number {
        font-size: 2rem;
    }
    
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
    
    .minimal-table {
        font-size: 0.8rem;
    }
    
    .minimal-table th,
    .minimal-table td {
        padding: 0.5rem 0.3rem;
    }
}

@media (max-width: 576px) {
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .action-btn {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
}

/* ANIMAÇÕES */
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

.section-card,
.norma-card,
.stats-card {
    animation: fadeIn 0.3s ease;
}

/* UTILITÁRIOS */
.table-responsive {
    border-radius: var(--border-radius);
    overflow: hidden;
}

.mb-4 { margin-bottom: 2rem; }
.mb-3 { margin-bottom: 1.5rem; }
.mb-2 { margin-bottom: 1rem; }

/* SCROLLBAR */
.table-responsive::-webkit-scrollbar {
    height: 6px;
}

.table-responsive::-webkit-scrollbar-track {
    background: var(--neutral-color);
}

.table-responsive::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: var(--secondary-color);
}

.execution-result::-webkit-scrollbar {
    width: 6px;
}

.execution-result::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.execution-result::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

.execution-result::-webkit-scrollbar-thumb:hover {
    background: var(--secondary-color);
}
</style>

<script>
let normaParaAtualizar = null;

function executarAtualizacao() {
    if (!confirm('Tem certeza que deseja executar a atualização de vigência? Esta ação irá alterar o status das normas que venceram.')) {
        return;
    }

    fetch('/vigencia/executar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ dry_run: false })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('resultadoExecucao').textContent = data.output || 'Nenhum resultado disponível';
        $('#modalResultado').modal('show');
        
        if (data.success) {
            // Recarregar a página após 2 segundos para mostrar os resultados atualizados
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao executar atualização');
    });
}

function atualizarNormaEspecifica(normaId, descricao) {
    normaParaAtualizar = normaId;
    document.getElementById('normaDetalhesAtualizacao').textContent = `ID: ${normaId} - ${descricao}`;
    $('#modalAtualizacao').modal('show');
}

document.getElementById('btnConfirmarAtualizacao').addEventListener('click', function() {
    if (!normaParaAtualizar) return;

    fetch(`/vigencia/atualizar-norma/${normaParaAtualizar}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        $('#modalAtualizacao').modal('hide');
        
        if (data.success) {
            alert('Norma atualizada com sucesso: ' + data.message);
            location.reload();
        } else {
            alert('Erro ao atualizar norma: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar norma');
    });
});
</script>

@endsection