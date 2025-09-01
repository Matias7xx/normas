@extends('layouts.app')

@section('page-title')
    Gestão de Boletins
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">
                <i class="fas fa-newspaper mr-2"></i>Gerência de Boletins
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Boletins</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <!-- Seção de Busca/Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-search mr-2"></i>Pesquisar Boletins
                        </h3>
                        {{-- <button type="button" id="toggleFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-filter mr-1"></i>
                            <span>Expandir Filtros</span>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body" id="filtersSection">
                    <form method="GET" action="{{ route('boletins.index') }}" id="searchForm">
                        <input type="hidden" name="busca" value="1">
                        
                        <div class="row">
                            <!-- Campo de busca por termo -->
                            <div class="col-md-6 mb-3">
                                <label for="search_term" class="form-label">
                                    <i class="fas fa-search mr-1 text-dark"></i>
                                    Buscar por nome ou descrição
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="search_term" 
                                       name="search_term"
                                       value="{{ $filtros['search_term'] ?? '' }}"
                                       placeholder="Ex: BSPC Nº 2158, 2160...">
                            </div>
                            
                            <!-- Data exata de publicação -->
                            <div class="col-md-3 mb-3">
                                <label for="data_publicacao" class="form-label">
                                    <i class="fas fa-calendar-day mr-1 text-dark"></i>
                                    Data Exata
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="data_publicacao" 
                                       name="data_publicacao"
                                       value="{{ $filtros['data_publicacao'] ?? '' }}"
                                       max="{{ date('Y-m-d') }}">
                            </div>
                            
                            <!-- Mês/Ano -->
                            <div class="col-md-3 mb-3">
                                <label for="mes_ano" class="form-label">
                                    <i class="fas fa-calendar-alt mr-1 text-dark"></i>
                                    Mês/Ano
                                </label>
                                <input type="month" 
                                       class="form-control" 
                                       id="mes_ano" 
                                       name="mes_ano"
                                       value="{{ $filtros['mes_ano'] ?? '' }}"
                                       max="{{ date('Y-m') }}">
                            </div>
                        </div>
                        
                        <!-- Botões de ação -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-primary" id="btnBuscar">
                                    <i class="fas fa-search mr-1"></i>Buscar
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnLimpar">
                                    <i class="fas fa-eraser mr-1"></i>Limpar Filtros
                                </button>
                                <button type="button" class="btn btn-outline-info" id="btnMesAtual">
                                    <i class="fas fa-calendar-check mr-1"></i>Mês Atual
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações da busca -->
    @if(isset($totalEncontrados))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-secondary-circle mr-1"></i>
                        <strong>{{ $totalEncontrados }}</strong> encontrado(s)
                        @if($mostrandoMesAtual ?? false)
                            <span class="ml-1 text-light">(do mês atual)</span>
                        @elseif(!empty(array_filter($filtros ?? [])))
                            <span class="ml-1 text-light
                            ">(com filtros aplicados)</span>
                        @endif
                    </div>
                    @if($boletins->hasPages())
                        <small class="text-muted">
                            Página {{ $boletins->currentPage() }} de {{ $boletins->lastPage() }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Boletins -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-list mr-2"></i>Lista de Boletins
                            </h3>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('boletins.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Novo Boletim
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($boletins->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="35%">Nome</th>
                                        <th width="20%">Descrição</th>
                                        <th width="13%">Data Publicação</th>
                                        <th width="15%">Cadastrado por</th>
                                        <th width="15%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($boletins as $boletim)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-pdf text-danger mr-2"></i>
                                                    <div>
                                                        <strong>{{ $boletim->nome }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $boletim->descricao ? Str::limit($boletim->descricao, 80) : '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span>
                                                    {{ $boletim->data_publicacao_formatada }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-user mr-1"></i>
                                                    {{ $boletim->usuario->name }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                {{-- Visualizar --}}
                                                <a href="{{ route('boletins.view', $boletim->id) }}" 
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-secondary mr-1"
                                                   title="Visualizar PDF">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                {{-- Download --}}
                                                <a href="{{ route('boletins.download', $boletim->id) }}" 
                                                   class="btn btn-sm btn-outline-secondary mr-1"
                                                   title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                
                                                {{-- Editar --}}
                                                <a href="{{ route('boletins.edit', $boletim->id) }}" 
                                                   class="btn btn-sm btn-outline-secondary mr-1"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                {{-- Excluir --}}
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmarExclusao({{ $boletim->id }}, '{{ $boletim->nome }}')"
                                                        title="Remover">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Paginação --}}
                        @if($boletins->hasPages())
                            <div class="card-footer">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <small class="text-muted">
                                            Exibindo {{ $boletins->firstItem() }} a {{ $boletins->lastItem() }} 
                                            de {{ $boletins->total() }} resultados
                                        </small>
                                    </div>
                                    <div class="col-auto">
                                        {{ $boletins->links() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            @if(!empty(array_filter($filtros ?? [])))
                                {{-- Estado vazio com filtros --}}
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum boletim encontrado</h5>
                                <p class="text-muted">
                                    @if($mostrandoMesAtual ?? false)
                                        Não há boletins publicados no mês atual.
                                    @else
                                        Tente ajustar os filtros de busca.
                                    @endif
                                </p>
                            @else
                                {{-- Estado vazio inicial --}}
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum boletim inserido</h5>
                                <p class="text-muted">Clique no botão "Novo Boletim" para começar.</p>
                                <a href="{{ route('boletins.create') }}" class="btn btn-dark">
                                    <i class="fas fa-plus"></i> Inserir Primeiro Boletim
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmação de exclusão --}}
    <div class="modal fade" id="modalExclusao" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                        Confirmar Remoção
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja remover o boletim <strong id="nomeBoletim"></strong>?</p>
                    <small class="text-muted">Esta ação não pode ser desfeita.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="formExclusao" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Confirmar Remoção
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Configuração inicial dos filtros
    const filtersSection = $('#filtersSection');
    const toggleFiltersBtn = $('#toggleFilters');
    
    // Inicializar sempre expandido
    let filtersVisible = {{ isset($expandirFiltros) && $expandirFiltros ? 'true' : 'true' }}; // Sempre true agora
    
    // Estado inicial dos filtros
    if (filtersVisible) {
        filtersSection.show();
        toggleFiltersBtn.find('span').text('Ocultar Filtros');
        toggleFiltersBtn.find('i').removeClass('fa-filter').addClass('fa-chevron-up');
    } else {
        filtersSection.hide();
        toggleFiltersBtn.find('span').text('Expandir Filtros');
        toggleFiltersBtn.find('i').removeClass('fa-chevron-up').addClass('fa-filter');
    }
    
    // Toggle dos filtros
    toggleFiltersBtn.click(function() {
        filtersVisible = !filtersVisible;
        
        if (filtersVisible) {
            filtersSection.slideDown();
            $(this).find('span').text('Ocultar Filtros');
            $(this).find('i').removeClass('fa-filter').addClass('fa-chevron-up');
        } else {
            filtersSection.slideUp();
            $(this).find('span').text('Expandir Filtros');
            $(this).find('i').removeClass('fa-chevron-up').addClass('fa-filter');
        }
    });
    
    // Limpar campo conflitante quando preencher outro
    $('#data_publicacao').change(function() {
        if ($(this).val()) {
            $('#mes_ano').val('');
        }
    });
    
    $('#mes_ano').change(function() {
        if ($(this).val()) {
            $('#data_publicacao').val('');
        }
    });
    
    // Botão Mês Atual
    $('#btnMesAtual').click(function() {
        // Limpar todos os filtros
        $('#search_term').val('');
        $('#data_publicacao').val('');
        $('#mes_ano').val('');
        
        // Definir mês atual
        const today = new Date();
        const currentMonth = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
        $('#mes_ano').val(currentMonth);
        
        // Submeter formulário
        $('#searchForm').submit();
    });
    
    // Botão Limpar Filtros
    $('#btnLimpar, #btnLimparVazio').click(function() {
        $('#search_term').val('');
        $('#data_publicacao').val('');
        $('#mes_ano').val('');
        $('.quick-filter').removeClass('active');
        
        // Redirecionar sem parâmetros de busca
        window.location.href = '{{ route("boletins.index") }}';
    });
    
    // Validação do formulário
    $('#searchForm').submit(function(e) {
        const searchTerm = $('#search_term').val().trim();
        const dataPublicacao = $('#data_publicacao').val();
        const mesAno = $('#mes_ano').val();
        
        // Verificar se pelo menos um campo foi preenchido
        if (!searchTerm && !dataPublicacao && !mesAno) {
            e.preventDefault();
            alert('Por favor, preencha pelo menos um campo de busca.');
            return false;
        }
        
        // Verificar data futura
        if (dataPublicacao) {
            const inputDate = new Date(dataPublicacao);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (inputDate > today) {
                e.preventDefault();
                alert('Não é possível buscar por datas futuras.');
                return false;
            }
        }
        
        // Verificar mês futuro
        if (mesAno) {
            const [year, month] = mesAno.split('-');
            const inputMonth = new Date(year, month - 1);
            const currentMonth = new Date();
            currentMonth.setDate(1);
            currentMonth.setHours(0, 0, 0, 0);
            
            if (inputMonth > currentMonth) {
                e.preventDefault();
                alert('Não é possível buscar por meses futuros.');
                return false;
            }
        }
        
        // Adicionar indicador de loading
        const btnBuscar = $('#btnBuscar');
        btnBuscar.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin mr-1"></i>Buscando...');
        
        // Restaurar botão após timeout (caso de erro)
        setTimeout(function() {
            btnBuscar.prop('disabled', false)
                     .html('<i class="fas fa-search mr-1"></i>Buscar');
        }, 10000);
    });
    
    // Enter para submeter busca
    $('#search_term').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#searchForm').submit();
        }
    });
    
    // Auto-focus no campo de busca já que filtros são sempre visíveis
    setTimeout(function() {
        $('#search_term').focus();
    }, 300);
    
    // Destacar filtros ativos na inicialização
    const currentSearchTerm = '{{ $filtros["search_term"] ?? "" }}';
    const currentDataPublicacao = '{{ $filtros["data_publicacao"] ?? "" }}';
    const currentMesAno = '{{ $filtros["mes_ano"] ?? "" }}';
    
    // Destacar campos preenchidos
    if (currentSearchTerm) {
        $('#search_term').addClass('border-primary');
    }
    if (currentDataPublicacao) {
        $('#data_publicacao').addClass('border-primary');
    }
    if (currentMesAno) {
        $('#mes_ano').addClass('border-primary');
    }
    
    // Remover destaque quando campo for limpo
    $('#search_term, #data_publicacao, #mes_ano').on('input change', function() {
        if ($(this).val()) {
            $(this).addClass('border-primary');
        } else {
            $(this).removeClass('border-primary');
        }
    });
});

// Função para confirmar exclusão
function confirmarExclusao(id, nome) {
    $('#nomeBoletim').text(nome);
    $('#formExclusao').attr('action', '{{ route("boletins.index") }}/' + id);
    $('#modalExclusao').modal('show');
}

// Tooltips para botões
$(function () {
    $('[title]').tooltip();
});

// Mensagens de feedback
@if(session('success'))
    $(document).ready(function() {
        if (typeof toastr !== 'undefined') {
            toastr.success('{{ session('success') }}');
        } else {
            alert('Sucesso: {{ session('success') }}');
        }
    });
@endif

@if($errors->any())
    $(document).ready(function() {
        @foreach($errors->all() as $error)
            if (typeof toastr !== 'undefined') {
                toastr.error('{{ $error }}');
            } else {
                console.error('Erro: {{ $error }}');
            }
        @endforeach
    });
@endif
</script>
@endsection