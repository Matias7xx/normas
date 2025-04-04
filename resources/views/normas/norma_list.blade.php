@extends('layouts.app')
@section('page-title')
    Lista de Normas
@endsection

@section('scripts')
<script src="{{ asset('js/normas.js') }}"></script>
@endsection
@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Lista de Normas</h1>
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
                <div class="card shadow-sm">
                    <div class="card-body p-3">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="search_term" class="text-sm text-muted">Pesquisa</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="search_term" name="search_term" 
                                            placeholder="Descrição, resumo ou palavra-chave" 
                                            autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="tipo_id" class="text-sm text-muted">Tipo de Norma</label>
                                    <select class="form-control select2-sm" id="tipo_id" name="tipo_id" data-placeholder="Todos os tipos">
                                        <option value=""></option>
                                        @foreach($tipos as $tipo)
                                            <option value="{{ $tipo->id }}">
                                                {{ $tipo->tipo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="orgao_id" class="text-sm text-muted">Órgão</label>
                                    <select class="form-control select2-sm" id="orgao_id" name="orgao_id" data-placeholder="Todos os órgãos">
                                        <option value=""></option>
                                        @foreach($orgaos as $orgao)
                                            <option value="{{ $orgao->id }}">
                                                {{ $orgao->orgao }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group mb-0 text-right">
                                    <button type="button" class="btn btn-primary mr-1" id="btn-search">
                                        <i class="fas fa-search"></i> Pesquisar
                                    </button>
                                    <button type="button" id="clear-filters" class="btn btn-light border">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Barra de ações e informações -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted" id="info-container">
                            <i class="fas fa-spinner fa-spin"></i> Carregando...
                        </span>
                    </div>
                    <div>
                        @if(Auth::user()->role_id == 1 || Auth::user()->can('gestor'))
                        <a href="{{ route('normas.norma_create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Nova Norma
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Exibição das normas -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="normas-table">
                                <thead>
                                    <tr>
                                        <th width="10%" class="sortable" data-field="tipo">Tipo <i class="fas fa-sort text-muted"></i></th>
                                        <th width="10%" class="sortable" data-field="data">Data <i class="fas fa-sort text-muted"></i></th>
                                        <th width="20%" class="sortable" data-field="descricao">Norma <i class="fas fa-sort text-muted"></i></th>
                                        <th width="35%" class="sortable" data-field="resumo">Resumo <i class="fas fa-sort text-muted"></i></th>
                                        <th width="15%" class="sortable" data-field="orgao">Órgão <i class="fas fa-sort text-muted"></i></th>
                                        <th width="15%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="normas-body">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Carregando normas...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Paginação -->
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted" id="pagination-info">
                                Carregando paginação...
                            </div>
                            <div id="pagination-controls">
                                <!-- Controles de paginação -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mensagem quando não há dados -->
                <div id="no-data-message" class="alert alert-info d-none">
                    <i class="fas fa-info-circle"></i> Nenhuma norma encontrada com os critérios de busca informados.
                    <a href="#" id="inline-clear-filters" class="alert-link ml-2">Limpar filtros</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja remover esta norma?</p>
                    <p class="text-muted font-weight-bold" id="normaDesc" style="font-size: 14px;"></p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-danger">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    /* Estilos para a tabela */
    .table th {
        background-color: #f8f9fa;
        font-weight: 500;
        border-top: none;
        border-bottom: 1px solid #dee2e6;
        color: #495057;
    }
    
    .table tbody tr {
        border-bottom: 1px solid #f2f2f2;
        transition: background-color 0.15s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    /* Estilos para botões de ação */
    .btn-xs {
        padding: 0.25rem 0.4rem;
        font-size: 0.8rem;
        line-height: 1.4;
        border-radius: 0.2rem;
        margin: 0 2px;
    }
    
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 6px;
    }
    
    .action-buttons .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        transition: all 0.2s ease;
    }
    
    /* Estilos para selects */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.9rem + 2px) !important;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.9rem) !important;
    }
    
    /* Ajustes para filtros compactos */
    .select2-sm + .select2-container {
        font-size: 0.875rem;
    }
    
    /* Estilização para badges */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    .palavra-chave-more {
        cursor: pointer;
    }
    
    /* Estilos para paginação */
    .page-link {
        padding: 0.375rem 0.75rem;
        color: #007bff;
        background-color: #fff;
        border: 1px solid #dee2e6;
    }
    
    .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    /* Estilos para ordenação */
    .sortable {
        cursor: pointer;
    }
    
    .sortable:hover {
        background-color: #f5f5f5;
    }
    
    .sort-asc .fa-sort:before {
        content: "\f0de"; /* fa-sort-up */
    }
    
    .sort-desc .fa-sort:before {
        content: "\f0dd"; /* fa-sort-down */
    }
    
    /* Estilos para loading */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255,255,255,0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .text-truncate-custom {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
</style>
@endsection