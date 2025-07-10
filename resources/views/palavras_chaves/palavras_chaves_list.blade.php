@extends('layouts.app')

@section('title', 'Palavras-chave')

@section('content')
<div class="container-fluid">
    <div class="page-header fade-in mb-6">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="display-6 mb-2">
                        <i class="fas fa-tags mr-3"></i>Palavras-chave
                    </h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    <i class="fas fa-home mr-1"></i>Início
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Palavras-chave</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <a href="{{ route('palavras_chaves.palavras_chaves_create') }}" class="btn btn-secondary">
                                <i class="fas fa-plus me-1"></i>
                                Nova Palavra-chave
                            </a>
                        </div>
                        <div class="col-auto ms-auto">
                            <!-- Seletor de itens por página -->
                            <form method="GET" class="d-flex align-items-center">
                                <label for="per_page" class="form-label me-2 mb-0 text-muted small">
                                    <i class="fas fa-list me-1"></i>Por página:
                                </label>
                                <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($palavras_chave->count() > 0)
                        <!-- Informações de paginação -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Exibindo {{ $palavras_chave->firstItem() }} a {{ $palavras_chave->lastItem() }} 
                                de {{ $palavras_chave->total() }} palavras-chave
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Palavra-chave</th>
                                        <th width="40%">Normas Vinculadas</th>
                                        <th width="15%">Data de Cadastro</th>
                                        <th width="15%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($palavras_chave as $palavra)
                                        <tr>
                                            <td>{{ $palavra->id }}</td>
                                            <td>
                                                <strong>{{ $palavra->palavra_chave }}</strong>
                                            </td>
                                            <td>
                                                @if($palavra->normas_ativas_count > 0)
                                                    <div class="mb-1">
                                                        <span class="badge bg-success">
                                                            {{ $palavra->normas_ativas_count }} norma(s)
                                                        </span>
                                                    </div>
                                                    
                                                    @if($palavra->normas_ativas_count <= 2)
                                                        @if($palavra->normasAtivas->count() > 0)
                                                            @foreach($palavra->normasAtivas->take(2) as $norma)
                                                                <div class="small text-muted mb-1 d-flex justify-content-between align-items-center">
                                                                    <span>• {{ Str::limit($norma->descricao, 50) }}</span>
                                                                    <a href="{{ route('palavras_chaves.desvincular', [$palavra->id, $norma->id]) }}" 
                                                                       class="text-warning ml-2" 
                                                                       onclick="return confirm('Desvincular esta norma?')"
                                                                       title="Desvincular">
                                                                        <i class="fas fa-unlink"></i>
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="small text-warning">
                                                                ⚠️ Dados inconsistentes
                                                            </div>
                                                        @endif
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-outline-primary btn-sm mt-1" 
                                                                onclick="verNormas({{ $palavra->id }})">
                                                            Ver todas ({{ $palavra->normas_ativas_count }})
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Nenhuma norma vinculada</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    {{ $palavra->created_at ? $palavra->created_at->format('d/m/Y H:i') : 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-buttons">
                                                    <a href="{{ route('palavras_chaves.palavras_chaves_edit', $palavra->id) }}" 
                                                       class="btn btn-secondary btn-xs" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    @if($palavra->normas_ativas_count == 0)
                                                        <button type="button" 
                                                                class="btn btn-danger btn-xs"
                                                                title="Excluir"
                                                                onclick="confirmarExclusao({{ $palavra->id }}, '{{ $palavra->palavra_chave }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-secondary btn-xs" 
                                                                disabled 
                                                                title="Não pode excluir - tem normas vinculadas">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação  -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $palavras_chave->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhuma palavra-chave cadastrada</h5>
                            <p class="text-muted">Clique no botão "Nova Palavra-chave" para começar.</p>
                            <a href="{{ route('palavras_chaves.palavras_chaves_create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Cadastrar Primeira Palavra-chave
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Normas -->
<div class="modal fade" id="modalNormas" tabindex="-1" aria-labelledby="modalNormasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNormasLabel">
                    <i class="fas fa-list me-2"></i>
                    Normas Vinculadas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalNormasBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando normas...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExclusao" tabindex="-1" aria-labelledby="modalExclusaoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalExclusaoLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a palavra-chave:</p>
                <p><strong id="nomePalavraChave"></strong></p>
                <p class="text-warning">
                    <i class="fas fa-info-circle me-1"></i>
                    Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                </button>
                <a href="#" id="btnConfirmarExclusao" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>
                    Excluir
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Variáveis globais para os modais
let modalNormasInstance = null;
let modalExclusaoInstance = null;

// Função para ver normas vinculadas
function verNormas(id) {
    // Inicializar modal se não existir
    if (!modalNormasInstance) {
        modalNormasInstance = new bootstrap.Modal(document.getElementById('modalNormas'), {
            backdrop: true,
            keyboard: true
        });
    }
    
    // Resetar conteúdo do modal
    document.getElementById('modalNormasBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2">Carregando normas...</p>
        </div>
    `;
    
    // Mostrar modal
    modalNormasInstance.show();
    
    // Fazer requisição AJAX
    fetch(`/palavras_chaves/normas-vinculadas/${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            let html = '';
            if (data.success && data.normas && data.normas.length > 0) {
                html = `<div class="mb-3">
                    <strong>Palavra-chave:</strong> ${data.palavra_chave || 'N/A'}<br>
                    <strong>Total de normas:</strong> ${data.total || 0}
                </div>`;
                
                data.normas.forEach(norma => {
                    html += `
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>${norma.descricao}</strong>
                                ${norma.data ? `<br><small class="text-muted">Data: ${norma.data}</small>` : ''}
                            </div>
                            <a href="/palavras_chaves/desvincular/${id}/${norma.id}" 
                               class="btn btn-sm btn-outline-warning"
                               onclick="return confirmarDesvinculacao()">
                                <i class="fas fa-unlink me-1"></i>
                                Desvincular
                            </a>
                        </div>
                    `;
                });
            } else {
                html = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Nenhuma norma vinculada encontrada.
                    </div>
                `;
            }
            document.getElementById('modalNormasBody').innerHTML = html;
        })
        .catch(error => {
            console.error('Erro ao carregar normas:', error);
            document.getElementById('modalNormasBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Erro ao carregar normas.</strong><br>
                    <small>${error.message}</small>
                </div>
            `;
        });
}

// Função para confirmar desvinculação
function confirmarDesvinculacao() {
    return confirm('Tem certeza que deseja desvincular esta norma da palavra-chave?');
}

// Função para confirmar exclusão
function confirmarExclusao(id, nome) {
    // Inicializar modal se não existir
    if (!modalExclusaoInstance) {
        modalExclusaoInstance = new bootstrap.Modal(document.getElementById('modalExclusao'), {
            backdrop: true,
            keyboard: true
        });
    }
    
    // Preencher dados
    document.getElementById('nomePalavraChave').textContent = nome;
    document.getElementById('btnConfirmarExclusao').href = `/palavras_chaves/excluir/${id}`;
    
    // Mostrar modal
    modalExclusaoInstance.show();
}

// Função para fechar todos os modais
function fecharModais() {
    if (modalNormasInstance) {
        modalNormasInstance.hide();
    }
    if (modalExclusaoInstance) {
        modalExclusaoInstance.hide();
    }
}

// Event listeners quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alertas após 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.classList.contains('show')) {
                let alertInstance = bootstrap.Alert.getInstance(alert);
                if (alertInstance) {
                    alertInstance.close();
                } else {
                    alert.style.display = 'none';
                }
            }
        }, 5000);
    });
    
    // Event listener para ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModais();
        }
    });
    
    // Event listeners para botões de fechar
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-bs-dismiss="modal"], [data-dismiss="modal"]') || 
            e.target.closest('[data-bs-dismiss="modal"], [data-dismiss="modal"]')) {
            fecharModais();
        }
    });
    
    // Limpar instâncias quando modais são fechados
    const modalNormasEl = document.getElementById('modalNormas');
    const modalExclusaoEl = document.getElementById('modalExclusao');
    
    if (modalNormasEl) {
        modalNormasEl.addEventListener('hidden.bs.modal', function() {
            modalNormasInstance = null;
        });
    }
    
    if (modalExclusaoEl) {
        modalExclusaoEl.addEventListener('hidden.bs.modal', function() {
            modalExclusaoInstance = null;
        });
    }
});
</script>
@endsection

<style>
    .page-header {
        background: linear-gradient(135deg, #404040 0%, #2c2c2c 100%);
        color: white;
        padding: 1.5rem 0;
        margin-bottom: 1rem;
        border-radius: 8px;
    }
    
    .page-header h2 {
        margin: 0;
        font-weight: 300;
    }
    
    .page-header .breadcrumb {
        background: transparent;
        margin: 0;
    }
    
    .page-header .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    
    .page-header .breadcrumb-item a:hover {
        color: white;
    }
    
    .page-header .breadcrumb-item.active {
        color: rgba(255,255,255,0.9);
    }

    /* Botões de ação */
    .btn-xs {
        padding: 0.375rem 0.5rem;
        font-size: 0.8rem;
        line-height: 1.4;
        border-radius: 6px;
        margin: 0 2px;
        transition: all 0.2s ease;
    }
    
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 2px;
        flex-wrap: wrap;
    }
    
    .action-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
            gap: 4px;
        }
        
        .btn-xs {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }
    }

    /* Estilo para paginação */
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        color: #6c757d;
        border-color: #dee2e6;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #404040;
        border-color: #404040;
    }
    
    .pagination .page-link:hover {
        color: #404040;
        background-color: #e9ecef;
    }

    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-dialog {
        margin-top: 3rem;
    }
    
    .spinner-border {
        width: 2rem;
        height: 2rem;
    }
</style>