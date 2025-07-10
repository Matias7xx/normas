@extends('layouts.app')

@section('title', 'Tipos de Norma')

@section('content')
<div class="container-fluid">
    <div class="page-header fade-in mb-6">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="display-6 mb-2">
                        <i class="fas fa-newspaper mr-3"></i>Tipos de Norma
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
                            <li class="breadcrumb-item active">Tipos de Norma</li>
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
                            <a href="{{ route('tipos.tipo_create') }}" class="btn btn-secondary">
                                <i class="fas fa-plus me-1"></i>
                                Novo Tipo de Norma
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

                    @if($tipo->count() > 0)
                        <!-- Informações de paginação -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Exibindo {{ $tipo->firstItem() }} a {{ $tipo->lastItem() }} 
                                de {{ $tipo->total() }} tipos de norma
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="70%">Nome do Tipo de Norma</th>
                                        <th width="20%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tipo as $tipo_obj)
                                        <tr>
                                            <td>{{ $tipo_obj->id }}</td>
                                            <td>
                                                <strong>{{ $tipo_obj->tipo }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-buttons">
                                                    <a href="{{ route('tipos.tipo_edit', $tipo_obj->id) }}" 
                                                       class="btn btn-secondary btn-xs" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-xs" 
                                                            title="Excluir"
                                                            onclick="confirmarExclusao({{ $tipo_obj->id }}, '{{ $tipo_obj->tipo }}')">
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
                            {{ $tipo->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum tipo de norma cadastrado</h5>
                            <p class="text-muted">Clique no botão "Novo Tipo de Norma" para começar.</p>
                            <a href="{{ route('tipos.tipo_create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Cadastrar Primeiro Tipo
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
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o tipo:</p>
                <p><strong id="nomeTipo"></strong></p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Atenção:</strong> Não será possível excluir se houver normas vinculadas a este tipo.
                </div>
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
    document.getElementById('nomeTipo').textContent = nome;
    document.getElementById('btnConfirmarExclusao').href = '{{ url("tipos/excluir") }}/' + id;
    
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