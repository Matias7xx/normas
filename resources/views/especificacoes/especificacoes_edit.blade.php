@extends('layouts.app')

@section('title', 'Editar Especificação')

@section('content')
<div class="container-fluid">

    <div class="page-header fade-in mb-6">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="display-6 mb-2">
                        <i class="fas fa-edit mr-3"></i>Editar Especificação
                    </h2>
                    <p class="lead mb-0">Altere a Especificação</p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    <i class="fas fa-home mr-1"></i>Início
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('especificacoes.especificacoes_list') }}">Especificações</a>
                            </li>
                            <li class="breadcrumb-item active">Alterar Especificação</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-edit text-dark mr-2"></i>
                        {{ $especificacao->nome }}
                    </h4>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('especificacoes.especificacoes_update', $especificacao->id) }}" method="POST" enctype="multipart/form-data" id="formEspecificacao">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="nome" class="form-label fw-bold">
                                        <i class="fas fa-tag mr-1"></i>
                                        Nome da Especificação <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('nome') is-invalid @enderror"
                                           id="nome"
                                           name="nome"
                                           value="{{ old('nome', $especificacao->nome) }}"
                                           placeholder="Ex: Câmera de Ação Portátil"
                                           maxlength="255"
                                           required>
                                    @error('nome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Digite um nome para a especificação técnica
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Arquivo atual -->
                        @if($especificacao->arquivo)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i>
                                            Arquivo Atual
                                        </label>
                                        <div class="card">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <div class="fw-medium">{{ $especificacao->arquivo }}</div>
                                                            <small class="text-muted">Enviado em {{ $especificacao->created_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('especificacoes.view', $especificacao->id) }}"
                                                           class="btn btn-sm btn-outline-danger mr-1"
                                                           target="_blank"
                                                           title="Visualizar PDF">
                                                            <i class="fas fa-eye"></i> Visualizar
                                                        </a>
                                                        <a href="{{ route('especificacoes.download', $especificacao->id) }}"
                                                           class="btn btn-sm btn-outline-dark"
                                                           title="Download PDF">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Upload novo arquivo -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label for="arquivo" class="form-label fw-bold">
                                        <i class="fas fa-file-pdf text-danger mr-1"></i>
                                        {{ $especificacao->arquivo ? 'Substituir Arquivo PDF' : 'Arquivo PDF da Especificação' }}
                                        @if(!$especificacao->arquivo)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="file"
                                           class="form-control @error('arquivo') is-invalid @enderror"
                                           id="arquivo"
                                           name="arquivo"
                                           accept=".pdf"
                                           {{ !$especificacao->arquivo ? 'required' : '' }}>
                                    @error('arquivo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        {{ $especificacao->arquivo ? 'Deixe em branco para manter o arquivo atual. ' : '' }}
                                        Apenas arquivos PDF são aceitos. Tamanho máximo: 10MB
                                    </div>

                                    <!-- Preview do novo arquivo selecionado -->
                                    <div id="arquivoPreview" class="mt-2 d-none">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                                            <strong>Novo arquivo:</strong>
                                            <span id="nomeArquivo"></span>
                                            <span id="tamanhoArquivo" class="text-muted ms-2"></span>
                                            <div class="mt-1">
                                                <small><i class="fas fa-exclamation-triangle mr-1"></i>Este arquivo substituirá o arquivo atual</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações de auditoria -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-body py-2">
                                        <h6 class="mb-2">
                                            <i class="fas fa-info-circle text-dark mr-1"></i>
                                            Informações de Auditoria
                                        </h6>
                                        <div class="row text-sm">
                                            <div class="col-md-6">
                                                <strong>Cadastrado por:</strong> {{ $especificacao->usuario->name ?? 'N/A' }}
                                                @if($especificacao->usuario && $especificacao->usuario->matricula)
                                                    <br><small class="text-muted">Matrícula: {{ $especificacao->usuario->matricula }}</small>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Data de Cadastro:</strong> {{ $especificacao->created_at->format('d/m/Y H:i') }}
                                                @if($especificacao->updated_at != $especificacao->created_at)
                                                    <br><small class="text-muted">Última atualização: {{ $especificacao->updated_at->format('d/m/Y H:i') }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('especificacoes.especificacoes_list') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-1"></i>
                                        Voltar
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary mr-2">
                                            <i class="fas fa-undo mr-1"></i>
                                            Resetar
                                        </button>
                                        <button type="submit" class="btn btn-dark" id="btnSalvar">
                                            <i class="fas fa-save mr-1"></i>
                                            Atualizar Especificação
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputArquivo = document.getElementById('arquivo');
    const arquivoPreview = document.getElementById('arquivoPreview');
    const nomeArquivo = document.getElementById('nomeArquivo');
    const tamanhoArquivo = document.getElementById('tamanhoArquivo');
    const btnSalvar = document.getElementById('btnSalvar');
    const form = document.getElementById('formEspecificacao');

    // Preview do arquivo selecionado
    inputArquivo.addEventListener('change', function(e) {
        const arquivo = e.target.files[0];

        if (arquivo) {
            // Verificar se é PDF
            if (arquivo.type !== 'application/pdf') {
                alert('Por favor, selecione apenas arquivos PDF.');
                this.value = '';
                arquivoPreview.classList.add('d-none');
                return;
            }

            // Verificar tamanho (10MB = 10 * 1024 * 1024 bytes)
            if (arquivo.size > 10 * 1024 * 1024) {
                alert('O arquivo é muito grande. Tamanho máximo permitido: 10MB');
                this.value = '';
                arquivoPreview.classList.add('d-none');
                return;
            }

            // Mostrar preview
            nomeArquivo.textContent = arquivo.name;

            // Formatar tamanho do arquivo
            const tamanhoMB = (arquivo.size / (1024 * 1024)).toFixed(2);
            tamanhoArquivo.textContent = `(${tamanhoMB} MB)`;

            arquivoPreview.classList.remove('d-none');
        } else {
            arquivoPreview.classList.add('d-none');
        }
    });

    // Loading state no botão de salvar
    form.addEventListener('submit', function() {
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Atualizando...';
    });

    // Esconder alertas após 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.classList.contains('show')) {
                bootstrap.Alert.getInstance(alert)?.close();
            }
        }, 5000);
    });
});
</script>
@endsection

<style>
.page-header {
        background: linear-gradient(135deg, #404040 0%, #2c2c2c 100%);
        color: white;
        padding: 1rem 0;
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
</style>
