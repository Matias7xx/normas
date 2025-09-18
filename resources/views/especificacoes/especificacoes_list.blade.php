@extends('layouts.app')

@section('title', 'Especificações Técnicas')

@section('content')
<div class="container-fluid">
    <div class="page-header fade-in mb-6">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="display-6 mb-2">
                        <i class="fas fa-file-pdf mr-3"></i>Especificações Técnicas
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
                            <li class="breadcrumb-item active">Especificações</li>
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
                            <a href="{{ route('especificacoes.especificacoes_create') }}" class="btn btn-secondary">
                                <i class="fas fa-plus mr-1"></i>
                                Nova Especificação
                            </a>
                        </div>
                        <div class="col-auto ms-auto">
                            <!-- Seletor de itens por página -->
                            <form method="GET" class="d-flex align-items-center">
                                <label for="per_page" class="form-label mr-2 mb-0 text-muted small">
                                    <i class="fas fa-list mr-1"></i>Por página:
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
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($especificacoes->count() > 0)
                        <!-- paginação -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle mr-1"></i>
                                Exibindo {{ $especificacoes->firstItem() }} a {{ $especificacoes->lastItem() }}
                                de {{ $especificacoes->total() }} especificações
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="35%">Nome da Especificação</th>
                                        <th width="20%">Arquivo</th>
                                        <th width="15%">Cadastrado por</th>
                                        <th width="15%">Data de Cadastro</th>
                                        <th width="10%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($especificacoes as $especificacao)
                                        <tr>
                                            <td>{{ $especificacao->id }}</td>
                                            <td>
                                                <strong>{{ $especificacao->nome }}</strong>
                                            </td>
                                            <td>
                                                @if($especificacao->arquivo)
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-file-pdf text-danger mr-2"></i>
                                                        <span class="text-muted small">{{ $especificacao->arquivo }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Sem arquivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user text-muted mr-2"></i>
                                                    <div>
                                                        <div class="fw-medium">{{ $especificacao->usuario->name ?? 'N/A' }}</div>
                                                        @if($especificacao->usuario && $especificacao->usuario->matricula)
                                                            <small class="text-muted">Mat: {{ $especificacao->usuario->matricula }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    {{ $especificacao->created_at ? $especificacao->created_at->format('d/m/Y H:i') : 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-buttons">
                                                    @if($especificacao->arquivo)
                                                        <a href="{{ route('especificacoes.view', $especificacao->id) }}"
                                                           class="btn btn-secondary btn-xs"
                                                           title="Visualizar PDF"
                                                           target="_blank">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('especificacoes.especificacoes_edit', $especificacao->id) }}"
                                                       class="btn btn-secondary btn-xs"
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-danger btn-xs"
                                                            title="Excluir"
                                                            onclick="confirmarExclusao({{ $especificacao->id }}, '{{ $especificacao->nome }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $especificacoes->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhuma especificação cadastrada</h5>
                            <p class="text-muted">Clique no botão "Nova Especificação" para começar.</p>
                            <a href="{{ route('especificacoes.especificacoes_create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i>
                                Cadastrar Primeira Especificação
                            </a>
                        </div>
                    @endif
                </div>
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
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a especificação:</p>
                <p><strong id="nomeEspecificacao"></strong></p>
                <p class="text-warning">
                    <i class="fas fa-info-circle mr-1"></i>
                    Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <a href="#" id="btnConfirmarExclusao" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i>
                    Excluir
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Variável global para o modal
let modalExclusaoInstance = null;

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
    document.getElementById('nomeEspecificacao').textContent = nome;
    document.getElementById('btnConfirmarExclusao').href = '{{ route("especificacoes.excluir", "") }}/' + id;

    // Mostrar modal
    modalExclusaoInstance.show();
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
        if (e.key === 'Escape' && modalExclusaoInstance) {
            modalExclusaoInstance.hide();
        }
    });

    // Event listeners para botões de fechar
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-bs-dismiss="modal"], [data-dismiss="modal"]') ||
            e.target.closest('[data-bs-dismiss="modal"], [data-dismiss="modal"]')) {
            if (modalExclusaoInstance) {
                modalExclusaoInstance.hide();
            }
        }
    });

    // Limpar instância quando modal é fechado
    const modalExclusaoEl = document.getElementById('modalExclusao');
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
</style>
