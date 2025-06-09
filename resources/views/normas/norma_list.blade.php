@extends('layouts.app')
@section('page-title')
    Lista de Normas
@endsection

@section('scripts')
<script>
    // Passar permissões do usuário para o JavaScript
    @if(isset($userPermissions))
        window.userPermissions = @json($userPermissions);
    @else
        window.userPermissions = {
            canEdit: false,
            canDelete: false,
            canCreate: false,
            isRoot: false,
            isAdmin: false
        };
    @endif
</script>

@php
 $currentYear = date('Y');
 $startYear = 1950;
 $endYear = $currentYear;
@endphp

<script src="{{ asset('js/normas.js') }}"></script>
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">
                <i class="fas fa-search mr-2"></i>
                Pesquisa de Normas
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Normas</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Filtros aprimorados -->
        <div class="row mb-3">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-filter mr-2"></i>
                    Filtros de Pesquisa
                </h5>
            </div>
            <div class="card-body">
                <!-- Pesquisa e Filtros Básicos -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="search_term" class="form-label font-weight-bold">
                            <i class="fas fa-search mr-1"></i> Pesquisa Geral
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" id="search_term" name="search_term" 
                                placeholder="Descrição, resumo ou palavra-chave..." 
                                autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="clear-search">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Busca em descrição, resumo e palavras-chave</small>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="tipo_id" class="form-label font-weight-bold">
                            <i class="fas fa-tags mr-1"></i> Tipo de Norma
                        </label>
                        <select class="form-control" id="tipo_id" name="tipo_id" data-placeholder="Todos os tipos">
                            <option value=""></option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->id }}">
                                    {{ $tipo->tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="orgao_id" class="form-label font-weight-bold">
                            <i class="fas fa-building mr-1"></i> Órgão
                        </label>
                        <select class="form-control" id="orgao_id" name="orgao_id" data-placeholder="Todos os órgãos">
                            <option value=""></option>
                            @foreach($orgaos as $orgao)
                                <option value="{{ $orgao->id }}">
                                    {{ $orgao->orgao }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="vigente" class="form-label font-weight-bold">
                            <i class="fas fa-gavel mr-1"></i> Status de Vigência
                        </label>
                        <select class="form-control" id="vigente" name="vigente" data-placeholder="Todos os status">
                            <option value=""></option>
                            @foreach(\App\Models\Norma::getVigenteOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">
                            <i class="fas fa-calendar-alt mr-1"></i> Período
                        </label>
                        <button type="button" class="btn btn-outline-secondary btn-block" id="toggle-period-filter">
                            <i class="fas fa-chevron-down" id="period-filter-icon"></i>
                            <span class="ml-1">Filtro por Período</span>
                        </button>
                    </div>
                </div>
                
                <!-- Seção de Período -->
                <div id="period-filter-content" style="display: none;">
                    <div class="border rounded p-3 bg-light">
                        <h6 class="font-weight-bold mb-3">
                            <i class="fas fa-calendar-alt mr-2 text-dark"></i>
                            Configurar Período de Busca
                        </h6>
                        
                        <div class="row">
                            <!-- Data de Início -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold text-muted">
                                        <i class="fas fa-play mr-1"></i> Data de Início
                                    </label>
                                    <div class="row">
                                        <div class="col-6">
                                            <select class="form-control" id="data_inicio_mes">
                                                <option value="">Mês</option>
                                                <option value="01">Janeiro</option>
                                                <option value="02">Fevereiro</option>
                                                <option value="03">Março</option>
                                                <option value="04">Abril</option>
                                                <option value="05">Maio</option>
                                                <option value="06">Junho</option>
                                                <option value="07">Julho</option>
                                                <option value="08">Agosto</option>
                                                <option value="09">Setembro</option>
                                                <option value="10">Outubro</option>
                                                <option value="11">Novembro</option>
                                                <option value="12">Dezembro</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <select class="form-control" id="data_inicio_ano">
                                                <option value="">Ano</option>
                                                @for ($year = date('Y'); $year >= 1950; $year--)
                                                    <option value="{{ $year }}">{{ $year }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Informe mês e ano para habilitar data de fim
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Data de Fim -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-muted" id="end-date-label">
                                        <i class="fas fa-stop mr-1"></i> Data de Fim
                                    </label>
                                    <div class="row">
                                        <div class="col-6">
                                            <select class="form-control disabled-field" id="data_fim_mes" disabled>
                                                <option value="">Mês</option>
                                                <option value="01">Janeiro</option>
                                                <option value="02">Fevereiro</option>
                                                <option value="03">Março</option>
                                                <option value="04">Abril</option>
                                                <option value="05">Maio</option>
                                                <option value="06">Junho</option>
                                                <option value="07">Julho</option>
                                                <option value="08">Agosto</option>
                                                <option value="09">Setembro</option>
                                                <option value="10">Outubro</option>
                                                <option value="11">Novembro</option>
                                                <option value="12">Dezembro</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <select class="form-control disabled-field" id="data_fim_ano" disabled>
                                                <option value="">Ano</option>
                                                @for ($year = date('Y'); $year >= 1950; $year--)
                                                    <option value="{{ $year }}">{{ $year }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-lock mr-1"></i>
                                        Bloqueado até data de início ser preenchida
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filtros Rápidos de Período -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <label class="form-label font-weight-bold text-muted">
                                    <i class="fas fa-bolt mr-1"></i> Filtros Rápidos:
                                </label>
                                <div class="btn-group btn-group-sm d-flex flex-wrap" role="group">
                                    <button type="button" class="btn btn-outline-secondary quick-filter" data-period="month">
                                        <i class="fas fa-calendar-alt mr-1"></i> Este Mês
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary quick-filter" data-period="quarter">
                                        <i class="fas fa-calendar mr-1"></i> Últimos 3 Meses
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary quick-filter" data-period="year">
                                        <i class="fas fa-calendar-check mr-1"></i> Este Ano
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botões de Ação -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-primary mr-2" id="btn-search">
                                    <i class="fas fa-search mr-1"></i> Pesquisar
                                </button>
                                <button type="button" id="clear-filters" class="btn btn-outline-secondary">
                                    <i class="fas fa-eraser mr-1"></i> Limpar Todos os Filtros
                                </button>
                            </div>
                            
                            @if(($userPermissions['canCreate'] ?? false))
                            <div>
                                <a href="{{ route('normas.norma_create') }}" class="btn btn-secondary">
                                    <i class="fas fa-plus mr-1"></i> Nova Norma
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        
        <!-- Barra de informações e resultados -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted" id="info-container">
                            <i class="fas fa-spinner fa-spin"></i> Carregando...
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <small class="text-muted mr-2">Ordenar por:</small>
                        <select id="sort-select" class="form-control form-control-sm mr-2" style="width: auto;">
                            <option value="data-desc" selected>Mais Recentes</option>
                            <option value="data-asc">Mais Antigas</option>
                            <option value="vigente-asc">Vigente</option>
                            <option value="vigente-desc">Não Vigente</option>
                            <option value="id-desc">ID (Maior para Menor)</option>
                            <option value="id-asc">ID (Menor para Maior)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabela de normas -->
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="normas-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%" class="sortable border-0" data-field="id" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                ID 
                                                <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </th>
                                        <th width="8%" class="sortable border-0" data-field="tipo" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                Tipo 
                                                <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </th>
                                        <th width="8%" class="sortable border-0" data-field="data" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                Data 
                                                <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </th>
                                        <th width="12%" class="sortable border-0" data-field="vigente" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                Vigência 
                                                <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </th>
                                        <th width="22%" class="sortable border-0" data-field="descricao" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                Norma 
                                                <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </th>
                                        <th width="25%" class="sortable border-0" data-field="resumo" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                Resumo 
                                                <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </th>
                                        <th width="10%" class="sortable border-0" data-field="orgao" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                Órgão 
                                                <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </th>
                                        <th width="10%" class="text-center border-0">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="normas-body">
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Carregando normas...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Paginação -->
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div id="pagination-controls">
                                <!-- Controles de paginação -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mensagem quando não há dados -->
                <div id="no-data-message" class="alert alert-secondary d-none mt-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x mr-3 text-dark"></i>
                        <div>
                            <h5 class="alert-heading mb-1">Nenhuma norma encontrada</h5>
                            <p class="mb-2">Não foram encontradas normas com os critérios de busca informados.</p>
                            <a href="#" id="inline-clear-filters" class="alert-link">
                                <i class="fas fa-eraser mr-1"></i> Limpar filtros e ver todas as normas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    @if(($userPermissions['canDelete'] ?? false))
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <h5>Tem certeza que deseja remover esta norma?</h5>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Atenção:</strong> Esta ação não pode ser desfeita.
                    </div>
                    <div class="bg-light p-3 rounded">
                        <strong>Norma:</strong>
                        <p class="mb-0 text-muted" id="normaDesc" style="font-size: 14px;"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i> Confirmar Exclusão
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('styles')
<style>
    /* Estilos aprimorados para a interface */
    .card {
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.9rem;
        color: #495057;
        padding: 1rem 0.75rem;
    }
    
    .table tbody tr {
        border-bottom: 1px solid #f2f2f2;
        transition: all 0.2s ease;
    }
    
    .sort-asc .fa-sort:before {
        content: "\f0de"; /* fa-sort-up */
        color: #007bff;
    }
    
    .sort-desc .fa-sort:before {
        content: "\f0dd"; /* fa-sort-down */
        color: #007bff;
    }
    
    /* Estilos para truncar texto */
    .text-truncate-custom {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255,255,255,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        border-radius: 10px;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .quick-filters {
            text-align: center;
        }
        
        .table-responsive {
            border-radius: 10px;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 4px;
        }
        
        .btn-xs {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }
    }
    
    /* Animações */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card {
        animation: fadeIn 0.5s ease-out;
    }
    
    /* Input focus */
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        transform: scale(1.02);
        transition: all 0.2s ease;
    }
    
    /* Background gradient para header */
    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40 0%, #212529 100%);
    }
    
    /* Melhorias no modal */
    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .modal-header {
        border-radius: 10px 10px 0 0;
    }
    
    /* Estilo para botões de ordenação ativos */
    .btn-group .btn.active {
        background-color: #007bff;
        color: white;
        transform: scale(1.05);
    }

    /* Estilos específicos para badges de vigência com ícones */
    .vigencia-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .vigencia-badge i {
        font-size: 0.8rem;
    }

    /* Hover effect para as linhas da tabela com base na vigência */
    /* .table tbody tr.norma-vigente:hover {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .table tbody tr.norma-nao-vigente:hover {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .table tbody tr.norma-em-analise:hover {
        background-color: rgba(255, 193, 7, 0.1);
    } */

    .table-responsive {
    -ms-overflow-style: none; /* IE e Edge */
    scrollbar-width: none; /* Firefox */
}

    .table-responsive::-webkit-scrollbar {
    display: none; /* Chrome, Safari e Opera */
}

    @endsections  ease;
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: scale(1.01);
    }
    
    /* Botões de ação aprimorados */
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
    
    /* Badges para status de vigência */
    .badge-vigente {
        background-color: #28a745;
        color: white;
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 15px;
        font-weight: 500;
    }
    
    .badge-nao-vigente {
        background-color: #dc3545;
        color: white;
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 15px;
        font-weight: 500;
    }
    
    .badge-em-analise {
        background-color: #ffc107;
        color: #212529;
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 15px;
        font-weight: 500;
    }
    
    /* Filtros rápidos */
    .quick-filters .btn {
        margin: 2px;
        border-radius: 20px;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }
    
    .quick-filters .btn.active {
        background-color: #007bff;
        color: white;
        transform: scale(1.05);
    }
    
    /* Select2 */
    .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
        border: 1px solid #ced4da;
        border-radius: 6px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + 0.75rem) !important;
        padding-left: 12px;
    }
    
    /* Badges personalizados */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        border-radius: 6px;
        font-size: 0.75rem;
    }
    
    .badge-light {
        background-color: #f8f9fa;
        color: #495057;
        border: 1px solid #dee2e6;
    }
    
    /* Paginação */
    .pagination {
        margin: 0;
    }
    
    .page-link {
        padding: 0.5rem 0.75rem;
        color: #007bff;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin: 0 2px;
        transition: all 0.2s ease;
    }
    
    .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
        transform: scale(1.1);
    }
    
    .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #dee2e6;
        transform: translateY(-1px);
    }
    
    /* Ordenação */
    .sortable:hover {
        background-color: rgba(0, 123, 255, 0.1);
        border-radius: 6px;
        transition: all 0.2
    }
</style>