@extends('layouts.app')

@section('page-title')
    Gerenciar Indexação
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">
                <i class="fas fa-sync-alt mr-2"></i>Gerenciamento de Indexação
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('boletins.index') }}">Boletins</a></li>
                <li class="breadcrumb-item active">Indexação</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Card de Estatísticas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>Estatísticas de Indexação
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-file-pdf"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total de Boletins</span>
                                    <span class="info-box-number">{{ $stats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Indexados</span>
                                    <span class="info-box-number">{{ $stats['indexados'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pendentes</span>
                                    <span class="info-box-number">{{ $stats['pendentes'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de Ações -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs mr-2"></i>Ações de Indexação
                    </h3>
                </div>
                <div class="card-body">
                    @if($stats['pendentes'] > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Existem <strong>{{ $stats['pendentes'] }}</strong> boletins pendentes de indexação.
                    </div>

                    <form action="{{ route('boletins.indexacao.iniciar') }}" method="POST" id="formIndexarPendentes">
                        @csrf
                        <input type="hidden" name="tipo" value="pendentes">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-play mr-2"></i>
                            Indexar Boletins Pendentes ({{ $stats['pendentes'] }})
                        </button>
                    </form>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Importante:</strong> Este processo pode levar alguns minutos dependendo do tamanho e quantidade dos PDFs.
                        Aguarde a conclusão.
                    </div>
                    @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        Todos os boletins estão indexados!
                    </div>
                    @endif

                    <hr class="my-4">

                    <h5>Re-indexar Todos</h5>
                    <p class="text-muted">Use esta opção se precisar re-indexar todos os boletins (inclusive os já indexados).</p>
                    <form action="{{ route('boletins.indexacao.iniciar') }}" method="POST"
                          id="formReindexarTodos"
                          onsubmit="return confirm('Tem certeza que deseja re-indexar TODOS os {{ $stats['total'] }} boletins? Isso pode levar bastante tempo.')">
                        @csrf
                        <input type="hidden" name="tipo" value="todos">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-redo mr-2"></i>
                            Re-indexar Todos os Boletins ({{ $stats['total'] }})
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card de Ajuda -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle mr-2"></i>Como Funciona?
                    </h3>
                </div>
                <div class="card-body">
                    <h5>O que é Indexação?</h5>
                    <p>A indexação extrai o conteúdo textual dos PDFs para permitir buscas rápidas, como a funcionalidade "Boletins com Meu Nome".</p>

                    <h5 class="mt-3">Quando é Necessário?</h5>
                    <ul>
                        <li><strong>Boletins Existentes:</strong> Use esta interface para indexar boletins cadastrados antes da implementação desta funcionalidade.</li>
                        <li><strong>Boletins Novos:</strong> São indexados automaticamente ao serem cadastrados ou editados.</li>
                    </ul>

                    <h5 class="mt-3">Tempo de Processamento</h5>
                    <ul>
                        <li><strong>PDFs com texto nativo:</strong> ~5-10 segundos por boletim</li>
                        <li><strong>PDFs escaneados (imagem):</strong> ~30-120 segundos por boletim (usa OCR)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Adicionar loading nos botões ao submeter
    $('#formIndexarPendentes, #formReindexarTodos').submit(function() {
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin mr-2"></i>Processando... Aguarde');
    });
});
</script>
@endsection
