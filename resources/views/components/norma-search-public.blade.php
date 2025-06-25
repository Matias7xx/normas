<script>
    // Configuração para consulta pública (sem permissões)
    window.userPermissions = {
        canEdit: false,
        canDelete: false,
        canCreate: false,
        isRoot: false,
        isAdmin: false
    };
    
    // Configuração específica para consulta pública
    window.isPublicSearch = true;
    window.ajaxRoute = '/norma_public_search_ajax';
</script>

@php
    $currentYear = date('Y');
    $startYear = 1950;
    $endYear = $currentYear;
@endphp

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
                
                <!-- Seção de Período (igual ao norma_list) -->
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
                                <th width="30%" class="sortable border-0" data-field="descricao" style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        Norma 
                                        <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                    </div>
                                </th>
                                <th width="35%" class="sortable border-0" data-field="resumo" style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        Resumo 
                                        <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                    </div>
                                </th>
                                <th width="15%" class="sortable border-0" data-field="orgao" style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        Órgão 
                                        <i class="fas fa-sort text-muted ml-1" style="font-size: 0.8rem;"></i>
                                    </div>
                                </th>
                                <th width="10%" class="text-center border-0">Visualizar</th>
                            </tr>
                        </thead>
                        <tbody id="normas-body">
                            <tr>
                                <td colspan="7" class="text-center py-4">
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