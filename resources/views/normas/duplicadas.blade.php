@extends('layouts.app')

@section('page-title')
    Normas Duplicadas
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">
                <i class="fas fa-copy mr-2"></i>
                Normas Duplicadas/Similares
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('normas.norma_list') }}">Normas</a></li>
                <li class="breadcrumb-item active">Duplicadas</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Filtro de Similaridade -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label>Similaridade Mínima</label>
                            <select name="similaridade" class="form-control">
                                <option value="70" {{ request('similaridade', 80) == 70 ? 'selected' : '' }}>70%</option>
                                <option value="75" {{ request('similaridade', 80) == 75 ? 'selected' : '' }}>75%</option>
                                <option value="80" {{ request('similaridade', 80) == 80 ? 'selected' : '' }}>80%</option>
                                <option value="85" {{ request('similaridade', 80) == 85 ? 'selected' : '' }}>85%</option>
                                <option value="90" {{ request('similaridade', 80) == 90 ? 'selected' : '' }}>90%</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(isset($duplicadas) && count($duplicadas) > 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Encontrados <strong>{{ count($duplicadas) }}</strong> grupos de normas similares.
        </div>

        @foreach($duplicadas as $grupo_index => $grupo)
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Grupo {{ $grupo_index + 1 }} - {{ count($grupo) }} normas similares
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($grupo as $norma)
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-header">
                                        <strong>ID: {{ $norma->id }}</strong>
                                        <span class="badge badge-{{ $norma->vigente == 'VIGENTE' ? 'success' : 'danger' }} float-right">
                                            {{ $norma->vigente }}
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $norma->descricao }}</h6>
                                        <p class="card-text small">
                                            <strong>Data:</strong> {{ $norma->data ? $norma->data->format('d/m/Y') : 'N/A' }}<br>
                                            <strong>Tipo:</strong> {{ $norma->tipo->tipo ?? 'N/A' }}<br>
                                            <strong>Órgão:</strong> {{ $norma->orgao->orgao ?? 'N/A' }}
                                        </p>
                                        <div class="btn-group btn-group-sm w-100">
                                            <a href="{{ route('normas.view', $norma->id) }}" 
                                               class="btn btn-light" 
                                               target="_blank">
                                                <i class="fas fa-eye"></i> Ver PDF
                                            </a>
                                            <a href="{{ route('normas.norma_edit', $norma->id) }}" 
                                               class="btn btn-light">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-dark" 
                                                    onclick="confirmarExclusao({{ $norma->id }}, '{{ $norma->descricao }}')">
                                                <i class="fas fa-trash"></i> Remover
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                <h4 class="mt-3 text-success">Nenhuma norma duplicada encontrada!</h4>
                <p class="text-muted">
                    Com {{ $similaridade_minima ?? 80 }}% de similaridade, não foram encontradas normas duplicadas.
                </p>
                <a href="{{ route('normas.norma_list') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar à Listagem
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta norma?</p>
                <p class="text-muted" id="normaDetalhes"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="formExclusao" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Confirmar Exclusão
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarExclusao(normaId, descricao) {
    document.getElementById('normaDetalhes').textContent = `ID: ${normaId} - ${descricao}`;
    document.getElementById('formExclusao').action = `/normas/norma_destroy/${normaId}`;
    $('#modalExclusao').modal('show');
}
</script>

@endsection