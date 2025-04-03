@extends('layouts.app')
@section('page-title')
    Lista de Normas
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
                        <form method="GET" action="{{ route('normas.norma_list') }}" id="search-form">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="search_term" class="text-sm text-muted">Pesquisa</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="search_term" name="search_term" 
                                                placeholder="Descrição, resumo ou palavra-chave" value="{{ request('search_term') }}"
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
                                                <option value="{{ $tipo->id }}" {{ request('tipo_id') == $tipo->id ? 'selected' : '' }}>
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
                                                <option value="{{ $orgao->id }}" {{ request('orgao_id') == $orgao->id ? 'selected' : '' }}>
                                                    {{ $orgao->orgao }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group mb-0 text-right">
                                        <button type="submit" class="btn btn-primary mr-1" id="btn-search">
                                            <i class="fas fa-search"></i> Pesquisar
                                        </button>
                                        <button type="button" id="clear-filters" class="btn btn-light border">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Barra de ações e informações -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Total: <span id="totalNormas" class="font-weight-bold">{{ $normas_por_tipo->sum(function($normas) { return count($normas); }) }}</span> normas
                        </span>
                        
                        @if(request('search_term') || request('tipo_id') || request('orgao_id'))
                        <span class="ml-2 badge badge-info">
                            <i class="fas fa-filter"></i> Filtros aplicados
                        </span>
                        @endif
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
                @if($normas_por_tipo->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhuma norma encontrada com os critérios de busca informados.
                        @if(request('search_term') || request('tipo_id') || request('orgao_id'))
                            <a href="#" id="inline-clear-filters" class="alert-link ml-2">Limpar filtros</a>
                        @endif
                    </div>
                @else
                    <div id="accordion" class="shadow-sm">
                        @php
                            $contador = 0;
                            $array_color = [
                                '#3c8dbc', // Azul
                                '#f39c12', // Laranja
                                '#00a65a', // Verde
                                '#f56954', // Vermelho
                                '#605ca8', // Roxo
                                '#00c0ef', // Ciano
                                '#d81b60', // Rosa
                                '#001f3f', // Azul escuro
                            ];
                        @endphp

                        @foreach($normas_por_tipo as $tipo_nome => $normas)
                            @php
                                $tipo_color = $array_color[$contador % count($array_color)];
                                $show_section = request('tipo_id') == $normas->first()->tipo_id || 
                                               request('search_term') || 
                                               ($contador === 0 && !request('tipo_id') && !request('search_term'));
                            @endphp
                            
                            <div class="card mb-2">
                                <a class="d-block w-100" data-toggle="collapse" href="#collapse-{{ $contador }}" role="button" aria-expanded="{{ $show_section ? 'true' : 'false' }}" aria-controls="collapse-{{ $contador }}">
                                    <div class="card-header py-2" style="background-color: {{ $tipo_color }}08; border-left: 3px solid {{ $tipo_color }};">
                                        <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="fas fa-angle-{{ $show_section ? 'down' : 'right' }} text-muted mr-2 toggle-icon"></i> {{ mb_strtoupper($tipo_nome) }}
                                            </span> 
                                            <span class="badge badge-pill" style="background-color: {{ $tipo_color }}; color: white;">{{ count($normas) }}</span>
                                        </h5>
                                    </div>
                                </a>
                                
                                <div id="collapse-{{ $contador }}" class="collapse {{ $show_section ? 'show' : '' }}" data-parent="#accordion">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover datatable-normas" id="table-{{ $contador }}">
                                                <thead>
                                                    <tr>
                                                        <th width="8%">Data</th>
                                                        <th width="34%">Descrição</th>
                                                        <th width="30%">Resumo</th>
                                                        <th width="18%">Órgão</th>
                                                        <th width="10%" class="text-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($normas as $norma)
                                                        <tr>
                                                            <td class="align-middle">{{ date('d/m/Y', strtotime($norma->data)) }}</td>
                                                            <td>
                                                                <div data-toggle="tooltip" title="{{ $norma->descricao }}" class="text-truncate" style="max-width: 350px;">
                                                                    {{ $norma->descricao }}
                                                                </div>
                                                                @if($norma->palavrasChave->count() > 0)
                                                                    <div class="mt-1">
                                                                        @foreach($norma->palavrasChave->take(2) as $pc)
                                                                            <span class="badge badge-info">{{ $pc->palavra_chave }}</span>
                                                                        @endforeach
                                                                        @if($norma->palavrasChave->count() > 2)
                                                                            <span class="badge badge-light palavra-chave-more" data-toggle="popover" 
                                                                                  data-content="{{ $norma->palavrasChave->skip(2)->pluck('palavra_chave')->join(', ') }}"
                                                                                  data-trigger="hover" data-placement="top">
                                                                                +{{ $norma->palavrasChave->count() - 2 }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div data-toggle="tooltip" title="{{ $norma->resumo }}" class="text-truncate" style="max-width: 300px;">
                                                                    {{ $norma->resumo }}
                                                                </div>
                                                            </td>
                                                            <td class="align-middle">{{ $norma->orgao->orgao }}</td>
                                                            <td class="text-center">
                                                                <div class="action-buttons">
                                                                    <a href='javascript:abrirPagina("{{ asset('storage/normas/'.$norma->anexo) }}",800,600);' 
                                                                        class="btn btn-xs btn-default" data-toggle="tooltip" title="Visualizar PDF">
                                                                        <i class="fas fa-file-pdf"></i>
                                                                    </a>
                                                                    
                                                                    @if(Auth::user()->role_id == 1 || Auth::user()->can('gestor'))
                                                                    <a href="{{ route('normas.norma_edit', $norma->id) }}" 
                                                                        class="btn btn-xs btn-primary" data-toggle="tooltip" title="Editar">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    
                                                                    <button type="button" class="btn btn-xs btn-danger delete-norma" 
                                                                            data-toggle="modal" data-target="#deleteModal" 
                                                                            data-norma-id="{{ $norma->id }}"
                                                                            data-norma-desc="{{ $norma->descricao }}"
                                                                            title="Remover">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $contador++;
                            @endphp
                        @endforeach
                    </div>
                @endif
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
    /* Estilos personalizados para paginação */
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 0.75em;
        padding-bottom: 0.75em;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 4px;
        margin: 0 2px;
        border: 1px solid #ddd !important;
        background: #fff !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f4f4f4 !important;
        border-color: #ccc !important;
        color: #333 !important;
    }
    
    /* Estilos para a tabela */
    .datatable-normas thead th {
        background-color: #f8f9fa;
        font-weight: 500;
        border-top: none;
        border-bottom: 1px solid #dee2e6;
        color: #495057;
    }
    
    .datatable-normas tbody tr {
        border-bottom: 1px solid #f2f2f2;
        transition: background-color 0.15s ease;
    }
    
    .datatable-normas tbody tr:hover {
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
    
    .btn-group .btn-xs + .btn-xs {
        margin-left: 4px;
    }
    
    /* Animação para o ícone de expansão */
    .card-header {
        transition: background-color 0.3s ease;
    }
    
    .card-header:hover {
        filter: brightness(0.95);
    }
    
    .toggle-icon {
        transition: transform 0.3s ease;
    }
    
    .collapse.show + .card-header .toggle-icon {
        transform: rotate(180deg);
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
    
    /* Estilos para espaçamento da tabela */
    .table-sm th, .table-sm td {
        padding: 0.5rem 0.75rem;
    }
    
    /* Caixa de busca personalizada nas tabelas */
    .dataTables_filter input {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 4px 8px;
        margin-left: 5px;
    }
    
    /* Estilo para cabeçalho de paginação */
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 0.5rem;
    }
    
    /* Ajuste para colunas em telas pequenas */
    @media (max-width: 767.98px) {
        .datatable-normas th, .datatable-normas td {
            white-space: nowrap;
        }
    }
    
    /* Estilização para badges */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    .palavra-chave-more {
        cursor: pointer;
    }
    
    /* Estilo para popovers */
    .popover {
        max-width: 300px;
        font-size: 0.8rem;
    }
    
    /* Estilos para os botões de ação */
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
</style>
@endsection

@section('scripts')
<script>
$(function () {
    // Inicializar tooltips com ajustes de desempenho
    $('[data-toggle="tooltip"]').tooltip({
        delay: { show: 500, hide: 100 },
        boundary: 'window',
        container: 'body'
    });
    
    // Inicializar popovers
    $('[data-toggle="popover"]').popover();
    
    // Inicializar Select2 com configuração melhorada
    $('.select2-sm').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 6,
        language: {
            noResults: function() {
                return "Nenhum resultado encontrado";
            },
            searching: function() {
                return "Pesquisando...";
            }
        }
    });
    
    // Configuração padrão para DataTables
    const dataTableConfig = {
        "responsive": true,
        "autoWidth": false,
        "ordering": true,
        "lengthChange": true,
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        "pagingType": "full_numbers",
        "language": {
            "search": "Filtrar:",
            "lengthMenu": "Exibir _MENU_",
            "zeroRecords": "Nenhum registro encontrado",
            "info": "Página _PAGE_ de _PAGES_",
            "infoEmpty": "Sem registros disponíveis",
            "infoFiltered": "(filtrado de _MAX_ registros)",
            "paginate": {
                "first": "<i class='fas fa-angle-double-left'></i>",
                "last": "<i class='fas fa-angle-double-right'></i>",
                "next": "<i class='fas fa-angle-right'></i>",
                "previous": "<i class='fas fa-angle-left'></i>"
            }
        },
        "columnDefs": [
            { "orderable": false, "targets": 4 } // Coluna de ações não ordenável
        ],
        "stateSave": true,
        "stateDuration": 60 * 60 * 24, // 1 dia
        "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        "initComplete": function() {
            // Adicionar classe para estilização
            $(this).closest('.dataTables_wrapper').addClass('dt-bootstrap4');
        }
    };
    
    // Inicializar DataTables para cada tabela
    const tableInstances = [];
    @foreach($normas_por_tipo as $key => $normas)
    tableInstances.push(
        $('#table-{{ $loop->index }}').DataTable(dataTableConfig)
    );
    @endforeach
    
    // Ajustar layout das tabelas após carregar
    $('.dataTables_wrapper').each(function() {
        $(this).find('.row:first-child').addClass('mb-2');
        $(this).find('.dataTables_filter input').addClass('form-control-sm');
        $(this).find('.dataTables_length select').addClass('form-control-sm');
    });
    
    // Melhorar comportamento do acordeão
    $('.card-header').on('click', function() {
        const icon = $(this).find('.toggle-icon');
        const isExpanded = $(this).closest('.card').find('.collapse').hasClass('show');
        
        if (isExpanded) {
            icon.removeClass('fa-angle-down').addClass('fa-angle-right');
        } else {
            icon.removeClass('fa-angle-right').addClass('fa-angle-down');
        }
    });
    
    // Configurar modal de exclusão com segurança aprimorada
    $('#deleteModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const normaId = button.data('norma-id');
        const normaDesc = button.data('norma-desc');
        
        const modal = $(this);
        modal.find('#normaDesc').text(normaDesc);
        
        // Atualizar action do formulário com token CSRF atualizado
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = '{{ route("normas.norma_destroy", ":id") }}'.replace(':id', normaId);
        
        // Garantir que o token CSRF está atualizado
        if (typeof csrfToken !== 'undefined') {
            $('input[name="_token"]').val(csrfToken);
        }
    });
    
    // Botão de limpar filtros sem recarregar a página
    $('#clear-filters').on('click', function(e) {
        e.preventDefault();
        
        // Feedback visual
        const $button = $(this);
        $button.html('<i class="fas fa-spinner fa-spin"></i>');
        $button.prop('disabled', true);
        
        // Limpar os campos do formulário
        $('#search_term').val('');
        
        // Resetar select2
        $('#tipo_id').val(null).trigger('change');
        $('#orgao_id').val(null).trigger('change');
        
        // Resetar os filtros de cada DataTable
        tableInstances.forEach(function(table) {
            table.search('').columns().search('').draw();
        });
        
        // Restaurar o estado do botão após pequeno delay
        setTimeout(function() {
            $button.html('<i class="fas fa-undo"></i>');
            $button.prop('disabled', false);
            
            // Mostrar notificação de sucesso
            const $notification = $('<div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 15px; right: 15px; z-index: 9999;">' +
                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                '<i class="fas fa-check-circle"></i> Filtros limpos com sucesso' +
                '</div>');
            
            $('body').append($notification);
            setTimeout(function() {
                $notification.alert('close');
            }, 3000);
            
        }, 500);
    });
    
    // Função para abrir documento em nova janela otimizada
    window.abrirPagina = function(url, largura, altura) {
        const left = (screen.width - largura) / 2;
        const top = (screen.height - altura) / 2;
        const opcoes = 'width=' + largura + ',height=' + altura + ',top=' + top + ',left=' + left + ',scrollbars=yes,resizable=yes,status=no,location=no';
        
        const win = window.open(url, '_blank', opcoes);
        if (win) {
            win.focus();
        } else {
            alert('Por favor, permita popups para este site para visualizar o documento.');
        }
        return false;
    };
    
    // Melhorar pesquisa global
    $('#btn-search').on('click', function() {
        const searchTerm = $('#search_term').val().trim();
        
        if (searchTerm.length > 0 && searchTerm.length < 3) {
            alert('Digite pelo menos 3 caracteres para pesquisar.');
            return false;
        }
        
        return true;
    });
    
    // Otimização para dispositivos móveis
    if (window.innerWidth < 768) {
        $('.datatable-normas').addClass('table-responsive');
    }
    
    // Remover tooltips quando modal é aberto para evitar sobreposição
    $('.modal').on('show.bs.modal', function () {
        $('.tooltip').remove();
    });
    
    // Link de limpar filtros inline
    $('#inline-clear-filters').on('click', function(e) {
        e.preventDefault();
        $('#clear-filters').click();
    });
    
    // Fechar popovers ao clicar fora
    $('body').on('click', function (e) {
        if ($(e.target).data('toggle') !== 'popover' && !$(e.target).parents('.popover').length) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });
    
    // Animar abertura das seções no carregamento inicial
    setTimeout(function() {
        $('.collapse.show').prev('.card-header').find('.toggle-icon').addClass('fa-angle-down').removeClass('fa-angle-right');
    }, 300);
});
</script>
@endsection