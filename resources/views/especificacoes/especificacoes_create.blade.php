@extends('layouts.app')

@section('title', 'Nova Especificação')

@section('content')
<div class="container-fluid">
    <div class="page-header fade-in mb-6">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="display-6 mb-2">
                        <i class="fas fa-plus-circle mr-3"></i>Nova Especificação
                    </h2>
                    <p class="lead mb-0">Insira uma nova especificação</p>
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
                            <li class="breadcrumb-item active">Nova Especificação</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('especificacoes.especificacoes_store') }}" method="POST" enctype="multipart/form-data" id="formEspecificacao">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="nome" class="form-label fw-bold">
                                        <i class="fas fa-tag me-1"></i>
                                        Nome da Especificação <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('nome') is-invalid @enderror" 
                                           id="nome" 
                                           name="nome" 
                                           value="{{ old('nome') }}" 
                                           placeholder="Ex: Workstation, Drone..."
                                           maxlength="255"
                                           required>
                                    @error('nome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Digite um nome para a especificação técnica
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label for="arquivo" class="form-label fw-bold">
                                        <i class="fas fa-file-pdf text-danger me-1"></i>
                                        Arquivo PDF da Especificação <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" 
                                           class="form-control @error('arquivo') is-invalid @enderror" 
                                           id="arquivo" 
                                           name="arquivo" 
                                           accept=".pdf"
                                           required>
                                    @error('arquivo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Apenas arquivos PDF são aceitos. Tamanho máximo: 10MB
                                    </div>
                                    
                                    <!-- Preview do arquivo selecionado -->
                                    <div id="arquivoPreview" class="mt-2 d-none">
                                        <div class="alert alert-secondary">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <span id="nomeArquivo"></span>
                                            <span id="tamanhoArquivo" class="text-muted ms-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('especificacoes.especificacoes_list') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        Voltar
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-undo me-1"></i>
                                            Limpar
                                        </button>
                                        <button type="submit" class="btn btn-dark" id="btnSalvar">
                                            <i class="fas fa-save me-1"></i>
                                            Inserir Especificação
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
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Salvando...';
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