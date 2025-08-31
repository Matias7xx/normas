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
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum boletim inserido</h5>
                            <p class="text-muted">Clique no botão "Novo Boletim" para começar.</p>
                            <a href="{{ route('boletins.create') }}" class="btn btn-dark">
                                <i class="fas fa-plus"></i> Inserir Primeiro Boletim
                            </a>
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
    function confirmarExclusao(id, nome) {
        $('#nomeBoletim').text(nome);
        $('#formExclusao').attr('action', `/boletins/${id}`);
        $('#modalExclusao').modal('show');
    }
</script>
@endsection