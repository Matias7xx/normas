@extends('layouts.app')
@section('page-title')
    Lista de Palavras-chave
@endsection
@section('header-content')
    <div class="page-header fade-in mb-6">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 mb-2">
                        <i class="fas fa-tags mr-3"></i>Lista de Palavras-chave
                    </h1>
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
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="{{ route('palavras_chaves.palavras_chaves_create') }}" class="btn btn-dark">
                    <i class="fas fa-plus"></i> Nova Palavra-chave
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-dark">
                    <div class="card-body">
                        <table id="palavras-chave-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="20%">Palavra-chave</th>
                                    <th width="50%">Normas Vinculadas</th>
                                    <th width="25%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($palavras_chave as $palavra_chave_obj)
                                    <tr>
                                        <td>{{ $palavra_chave_obj->id }}</td>
                                        <td>{{ $palavra_chave_obj->palavra_chave }}</td>
                                        <td>
                                            @if($palavra_chave_obj->normas_ativas_count > 0)
                                                <div class="mb-1">
                                                    <span class="badge badge-info">{{ $palavra_chave_obj->normas_ativas_count }} norma(s) vinculada(s)</span>
                                                </div>
                                                
                                                <!-- Lista de normas (limitada a 3) -->
                                                <div class="normas-list">
                                                    @foreach($palavra_chave_obj->normasAtivas->take(3) as $norma)
                                                        <div class="d-flex justify-content-between align-items-center mb-2 bg-light p-2 rounded">
                                                            <span class="mr-2 text-truncate" style="max-width: 70%;" title="{{ $norma->descricao }}">
                                                                {{ $norma->descricao }}
                                                            </span>
                                                            <a href="{{ route('palavras_chaves.desvincular', ['palavra_chave_id' => $palavra_chave_obj->id, 'norma_id' => $norma->id]) }}" 
                                                               class="btn btn-xs btn-warning"
                                                               onclick="return confirm('Tem certeza que deseja desvincular esta palavra-chave da norma?')">
                                                                <i class="fas fa-unlink"></i> Desvincular
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                
                                                <!-- Mostrar link "Ver todas" apenas se houver mais de 3 normas -->
                                                @if($palavra_chave_obj->normas_ativas_count > 3)
                                                    <a href="#" class="ver-mais-normas btn btn-sm btn-outline-primary mt-2" 
                                                       data-palavra-chave-id="{{ $palavra_chave_obj->id }}" 
                                                       data-toggle="modal" data-target="#modalTodasNormas">
                                                        <i class="fas fa-eye"></i> Ver todas as {{ $palavra_chave_obj->normas_ativas_count }} normas...
                                                    </a>
                                                @endif
                                            @else
                                                <span class="text-muted">Nenhuma norma vinculada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('palavras_chaves.palavras_chaves_edit', $palavra_chave_obj->id) }}" 
                                                   class="btn btn-dark mb-2">
                                                    <i class='fas fa-edit'></i> Editar
                                                </a>
                                                
                                                @if($palavra_chave_obj->normas_ativas_count == 0)
                                                    <a href="{{ route('palavras_chaves.excluir', $palavra_chave_obj->id) }}" 
                                                       class="btn btn-danger btn-excluir-palavra">
                                                        <i class='fas fa-trash'></i> Excluir
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-danger" disabled 
                                                            data-toggle="tooltip" 
                                                            title="Para excluir, primeiro desvincule de todas as normas">
                                                        <i class='fas fa-trash'></i> Excluir
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
    </div>
    
    <!-- Modal para exibir todas as normas vinculadas -->
    <div class="modal fade" id="modalTodasNormas" tabindex="-1" role="dialog" aria-labelledby="modalTodasNormasLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTodasNormasLabel">Normas vinculadas à palavra-chave</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="loading-normas" class="text-center my-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Carregando normas vinculadas...</p>
                    </div>
                    
                    <div id="lista-todas-normas" class="d-none">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Descrição da Norma</th>
                                    <th width="150">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-normas-vinculadas">
                                <!-- Preenchido via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(function () {
    // Inicialização da DataTable
    $('#palavras-chave-table').DataTable({
        "responsive": true, 
        "lengthChange": true, 
        "autoWidth": false,
        "pageLength": 25,
        "language": {
            "search": "Buscar:",
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "Nenhum resultado encontrado",
            "info": "Exibindo página _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhum registro disponível",
            "infoFiltered": "(filtrado de _MAX_ registros totais)",
            "paginate": {
                "first": "Primeiro",
                "last": "Último",
                "next": "Próximo",
                "previous": "Anterior"
            }
        }
    });
    
    // Tratamento do modal para exibir todas as normas
    $('.ver-mais-normas').on('click', function(e) {
        e.preventDefault();
        
        const palavraChaveId = $(this).data('palavra-chave-id');
        
        // Mostrar loading e esconder lista
        $('#loading-normas').removeClass('d-none');
        $('#lista-todas-normas').addClass('d-none');
        
        // Buscar normas vinculadas via AJAX
        $.ajax({
            url: `/palavras_chaves/normas-vinculadas/${palavraChaveId}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("Resposta do servidor:", response);
                
                // Esconder loading e mostrar lista
                $('#loading-normas').addClass('d-none');
                $('#lista-todas-normas').removeClass('d-none');
                
                // Preencher tabela com as normas
                let html = '';
                if (response.normas && response.normas.length > 0) {
                    response.normas.forEach(function(norma) {
                        html += `
                            <tr>
                                <td>${norma.descricao}</td>
                                <td>
                                    <a href="/palavras_chaves/desvincular/${palavraChaveId}/${norma.id}" 
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Tem certeza que deseja desvincular esta palavra-chave da norma?')">
                                        <i class="fas fa-unlink"></i> Desvincular
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = `<tr><td colspan="2" class="text-center">Nenhuma norma vinculada encontrada</td></tr>`;
                }
                
                $('#tbody-normas-vinculadas').html(html);
                
                // Atualizar título do modal com o nome da palavra-chave
                $('#modalTodasNormasLabel').text(`Normas vinculadas à palavra-chave "${response.palavra_chave}"`);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar normas:', error);
                console.error('Resposta:', xhr.responseJSON || xhr.responseText);
                
                // Esconder loading e mostrar mensagem de erro
                $('#loading-normas').addClass('d-none');
                $('#lista-todas-normas').removeClass('d-none');
                $('#tbody-normas-vinculadas').html(`
                    <tr>
                        <td colspan="2" class="text-center text-danger">
                            <p><i class="fas fa-exclamation-triangle mr-2"></i> Erro ao carregar normas.</p>
                            <p class="text-muted">${xhr.responseJSON?.error || error}</p>
                            <button class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="recarregarNormas(${palavraChaveId})">
                                <i class="fas fa-sync-alt"></i> Tentar novamente
                            </button>
                        </td>
                    </tr>
                `);
            }
        });
    });
    
    // Função para recarregar normas
    window.recarregarNormas = function(palavraChaveId) {
        const $botao = $(`.ver-mais-normas[data-palavra-chave-id="${palavraChaveId}"]`);
        if ($botao.length) {
            $botao.trigger('click');
        }
    };
    
    // Tratamento de confirmação para exclusão
    $('.btn-excluir-palavra').on('click', function(e) {
        return confirm('Tem certeza que deseja excluir permanentemente esta palavra-chave?');
    });
});
</script>
@endsection

<style>
.page-header {
        background: linear-gradient(135deg, #404040 0%, #2c2c2c 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 1rem;
        border-radius: 8px;
    }
    
    .page-header h1 {
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
</style>