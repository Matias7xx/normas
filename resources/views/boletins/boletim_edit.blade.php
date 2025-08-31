@extends('layouts.app')

@section('page-title')
    Editar Boletim
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">
                <i class="fas fa-edit mr-2"></i>Editar Boletim
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('boletins.index') }}">Boletins</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <form action="{{ route('boletins.update', $boletim->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                {{-- Card Principal --}}
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt mr-2"></i>Dados do Boletim
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-info">ID: {{ $boletim->id }}</span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            {{-- Nome do Boletim --}}
                            <div class="col-md-12 mb-3">
                                <label for="nome" class="form-label">
                                    <i class="fas fa-tag mr-1"></i>Nome do Boletim <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nome') is-invalid @enderror"
                                       id="nome"
                                       name="nome"
                                       value="{{ old('nome', $boletim->nome) }}"
                                       placeholder="Ex: BSPC EXTRAORDINARIO Nº 2146 - 01.08.2025"
                                       maxlength="255"
                                       required>
                                @error('nome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- Data de Publicação --}}
                            <div class="col-md-4 mb-3">
                                <label for="data_publicacao" class="form-label">
                                    <i class="fas fa-calendar mr-1"></i>Data de Publicação <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('data_publicacao') is-invalid @enderror"
                                       id="data_publicacao"
                                       name="data_publicacao"
                                       value="{{ old('data_publicacao', $boletim->data_publicacao->format('Y-m-d')) }}"
                                       required>
                                @error('data_publicacao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- Descrição --}}
                            <div class="col-md-12 mb-3">
                                <label for="descricao" class="form-label">
                                    <i class="fas fa-align-left mr-1"></i>Descrição
                                </label>
                                <textarea class="form-control @error('descricao') is-invalid @enderror"
                                          id="descricao"
                                          name="descricao"
                                          rows="4"
                                          maxlength="1000"
                                          placeholder="Descreva o conteúdo do boletim (opcional)...">{{ old('descricao', $boletim->descricao) }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="descricao-contador">0</span>/1000 caracteres
                                </small>
                                @error('descricao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Arquivo Atual --}}
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-pdf mr-2"></i>Arquivo Atual
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf text-danger fa-2x mr-3"></i>
                                    <div>
                                        <h6 class="mb-1">{{ $boletim->nome }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Publicado em: {{ $boletim->data_publicacao_formatada }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="{{ route('boletins.view', $boletim->id) }}" 
                                   target="_blank" 
                                   class="btn btn-dark btn-sm mr-2">
                                    <i class="fas fa-eye mr-1"></i>Visualizar
                                </a>
                                <a href="{{ route('boletins.download', $boletim->id) }}" 
                                   class="btn btn-secondary btn-sm">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Novo Arquivo --}}
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-upload mr-2"></i>Substituir Arquivo
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Opcional</span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="arquivo" class="form-label">
                                    <i class="fas fa-upload mr-1"></i>Novo Arquivo PDF (opcional)
                                </label>
                                
                                <div class="custom-file">
                                    <input type="file" 
                                           class="custom-file-input @error('arquivo') is-invalid @enderror"
                                           id="arquivo"
                                           name="arquivo"
                                           accept=".pdf">
                                    <label class="custom-file-label" for="arquivo">
                                        Escolher novo arquivo PDF...
                                    </label>
                                    @error('arquivo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Deixe em branco para manter o arquivo atual. Apenas arquivos PDF são aceitos. Tamanho máximo: 20MB
                                </small>
                                
                                {{-- Preview do arquivo selecionado --}}
                                <div id="arquivo-preview" class="mt-3" style="display: none;">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-file-pdf mr-2"></i>
                                        <strong>Novo arquivo selecionado:</strong>
                                        <span id="arquivo-nome"></span>
                                        <br>
                                        <small>
                                            <i class="fas fa-weight-hanging mr-1"></i>
                                            Tamanho: <span id="arquivo-tamanho"></span>
                                        </small>
                                        <br>
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Este arquivo substituirá o arquivo atual permanentemente.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Informações de Auditoria --}}
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>Informações
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-user mr-1"></i>
                                    <strong>Cadastrado por:</strong> {{ $boletim->usuario->name }}
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-plus mr-1"></i>
                                    <strong>Criado em:</strong> {{ $boletim->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-edit mr-1"></i>
                                    <strong>Atualizado em:</strong> {{ $boletim->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botões --}}
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-dark btn-lg mr-2">
                                    <i class="fas fa-save mr-2"></i>Atualizar Boletim
                                </button>
                                <a href="{{ route('boletins.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times mr-2"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Contador de caracteres para descrição
    $('#descricao').on('input', function() {
        var count = $(this).val().length;
        $('#descricao-contador').text(count);
        
        if (count > 900) {
            $('#descricao-contador').addClass('text-warning');
        } else {
            $('#descricao-contador').removeClass('text-warning');
        }
        
        if (count >= 1000) {
            $('#descricao-contador').addClass('text-danger').removeClass('text-warning');
        }
    });
    
    // Atualizar contador inicial
    $('#descricao').trigger('input');
    
    // Preview do arquivo selecionado
    $('#arquivo').on('change', function() {
        var file = this.files[0];
        if (file) {
            // Atualizar label
            $(this).next('.custom-file-label').text(file.name);
            
            // Mostrar preview
            $('#arquivo-nome').text(file.name);
            $('#arquivo-tamanho').text(formatFileSize(file.size));
            $('#arquivo-preview').show();
            
            // Validar tamanho (20MB = 20971520 bytes)
            if (file.size > 20971520) {
                alert('Arquivo muito grande! O tamanho máximo permitido é 20MB.');
                $(this).val('');
                $(this).next('.custom-file-label').text('Escolher novo arquivo PDF...');
                $('#arquivo-preview').hide();
                return;
            }
            
            // Validar tipo
            if (file.type !== 'application/pdf') {
                alert('Apenas arquivos PDF são permitidos!');
                $(this).val('');
                $(this).next('.custom-file-label').text('Escolher novo arquivo PDF...');
                $('#arquivo-preview').hide();
                return;
            }
        } else {
            $(this).next('.custom-file-label').text('Escolher novo arquivo PDF...');
            $('#arquivo-preview').hide();
        }
    });
    
    // Função para formatar tamanho do arquivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endsection