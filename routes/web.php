<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NormaController;
use App\Http\Controllers\OrgaoController;
use App\Http\Controllers\PalavraChaveController;
use App\Http\Controllers\TipoController;
use App\Http\Controllers\EspecificacaoController;
use App\Http\Controllers\NormaFileController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;
use App\Http\Controllers\BoletimController;
use App\Http\Controllers\VigenciaDashboardController;
use App\Http\Middleware\CheckAdminRoles;

// ==================== ROTAS PÚBLICAS (Vue 3 + Inertia.js) ====================

// Página inicial pública
Route::get('/', [PublicController::class, 'home'])->name('public.home');

// Consulta de normas
Route::get('/consulta', [PublicController::class, 'consulta'])->name('public.consulta');

// Visualização de norma específica
Route::get('/norma/{id}', [PublicController::class, 'normaView'])->name('public.norma.view');

// Visualização de PDF da norma (com iframe)
Route::get('/norma/{id}/view', [PublicController::class, 'viewNorma'])->name('public.norma.pdf.view');

// Download da norma
Route::get('/norma/{id}/download', [PublicController::class, 'downloadNorma'])->name('public.norma.download');

// API para busca de normas (AJAX)
Route::get('/api/normas/search', [PublicController::class, 'searchApi'])->name('api.normas.search');

// API para obter dados das páginas
Route::get('/api/stats', [PublicController::class, 'getStats'])->name('api.stats');
Route::get('/api/tipos', [PublicController::class, 'getTipos'])->name('api.tipos');
Route::get('/api/orgaos', [PublicController::class, 'getOrgaos'])->name('api.orgaos');

// ==================== ESPECIFICAÇÕES TÉCNICAS - ROTAS PÚBLICAS ====================

// Página pública de especificações
Route::get('/especificacoes', [PublicController::class, 'especificacoes'])->name('public.especificacoes');

// Download público de especificação
Route::get('/especificacao/download/{id}', [PublicController::class, 'downloadEspecificacao'])->name('public.especificacoes.download');

// Visualização pública de PDF de especificação
Route::get('/especificacao/view/{id}', [PublicController::class, 'viewEspecificacao'])->name('public.especificacoes.view');

//Search com blade
//Route::get('/consulta-lista', [NormaSearchPublicController::class, 'search'])->name('norma_public_search');

// rota AJAX para a consulta pública com BLADE
//Route::get('/norma_public_search_ajax', [NormaSearchPublicController::class, 'searchAjax'])->name('norma_public_search_ajax');

// ==================== BOLETINS - ROTAS PÚBLICAS ====================


Route::middleware([Authenticate::class])->group(function() {
    // Página de boletins com busca
    Route::get('/boletins', [PublicController::class, 'boletins'])->name('public.boletins');

    // Visualização de PDF de boletim
    Route::get('/boletim/view/{id}', [PublicController::class, 'viewBoletim'])->name('public.boletim.view');

    // Download de boletim
    Route::get('/boletim/download/{id}', [PublicController::class, 'downloadBoletim'])->name('public.boletim.download');
});

// ==================== ÁREA ADMINISTRATIVA ====================

Route::middleware([CheckAdminRoles::class])->group(function() {

    Route::get('/home', [NormaController::class, 'index'])->name('home');

    //Rota para servir as imagens do minIO
    Route::get('/foto-usuario/{cpf?}', function($cpf = null) {
        Log::info("=== DEBUG ROTA FOTO ===");
        Log::info("CPF recebido: " . ($cpf ?? 'null'));

        if (!Auth::check()) {
            Log::warning("Usuário não autenticado");
            return abort(404);
        }

        // Se não passou CPF, usa o do usuário logado
        if (!$cpf) {
            $cpf = str_replace(['.', '-'], '', Auth::user()->cpf ?? '');
            Log::info("CPF do usuário logado: {$cpf}");
        }

        $nomeArquivo = "{$cpf}_F.jpg";
        Log::info("Nome do arquivo: {$nomeArquivo}");

        try {
            // DEBUG: Configuração do disco
            $diskConfig = config('filesystems.disks.s3');
            Log::info("Configuração do disco:", $diskConfig);

            // Usar o disco s3 (bucket funcionais)
            $exists = StorageHelper::fotos()->exists($nomeArquivo);
            Log::info("Arquivo existe: " . ($exists ? 'SIM' : 'NÃO'));

            if ($exists) {
                $conteudo = StorageHelper::fotos()->get($nomeArquivo);
                $tamanho = strlen($conteudo);
                Log::info("Arquivo encontrado - Tamanho: {$tamanho} bytes");

                return response($conteudo, 200)
                    ->header('Content-Type', 'image/jpg')
                    ->header('Cache-Control', 'public, max-age=3600'); // Cache por 1 hora
            } else {
                // DEBUG: Listar o que tem no bucket
                try {
                    $files = Storage::disk('s3')->allFiles();
                    Log::info("Arquivos no bucket:", $files);
                } catch (\Exception $e) {
                    Log::error("Erro ao listar arquivos: " . $e->getMessage());
                }

                Log::warning("Arquivo não encontrado: {$nomeArquivo}");
                return abort(404);
            }

        } catch (\Exception $e) {
            Log::error("Erro na rota de foto: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return abort(500);
        }

    })->name('foto.usuario');

    // =====================  USUÁRIOS   ============================
    Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'root']], function(){
        Route::get('/users', [UserController::class, 'index'])->name('user.index');
        Route::get('/users/list/{id}',[UserController::class, 'userTaskList'])->name('user.list');
        Route::get('/users/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/users/store', [UserController::class, 'store'])->name('user.store');
        Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
        Route::post('/users/update/{id}', [UserController::class, 'update'])->name('user.update');
        Route::get('/users/activate/{id}', [UserController::class, 'activate'])->name('user.activate');
        Route::get('/users/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
        Route::get('/users/disable/{id}', [UserController::class, 'disable'])->name('user.disable');
        Route::post('/add-role', [AdminController::class, 'addRole'])->name('admin.addrole');
        Route::get('/list-roles', [AdminController::class, 'listRoles'])->name('admin.listrole');
        Route::get('/edit-role/{id}', [AdminController::class, 'editRole'])->name('admin.editrole');
        Route::post('/update-role/{id}', [AdminController::class, 'updateRole'])->name('admin.updaterole');
        Route::post('/add-permission', [AdminController::class, 'addPermission'])->name('admin.addpermission');
        Route::get('/list-permissions', [AdminController::class, 'listPermissions'])->name('admin.listpermission');
        Route::get('/delete-role/{id}', [AdminController::class, 'deleteRole'])->name('admin.deleterole');
    });

    // =====================  NORMAS   ============================
    Route::group(['prefix' => 'normas', 'middleware' => ['auth']], function(){
        // Listagem e pesquisa
        Route::get('/ajax', [NormaController::class, 'getNormasAjax'])->name('normas.ajax');
        Route::get('/norma_list', [NormaController::class, 'index'])->name('normas.norma_list');

        // ROTAS para arquivos no MinIO
        Route::get('/view/{id}', [NormaFileController::class, 'view'])->name('normas.view');
        Route::get('/download/{id}', [NormaFileController::class, 'download'])->name('normas.download');

       // Rota para normas duplicadas - apenas para (role 1 e 2)
        Route::get('/duplicadas', [NormaController::class, 'normasDuplicadas'])
        ->name('normas.duplicadas');

        // Verificação de grupos duplicados
        Route::post('/verificar-grupo-duplicadas', [NormaController::class, 'verificarGrupoDuplicadas'])
            ->name('normas.verificar_grupo_duplicadas');

        // Lista de grupos verificados
        Route::get('/grupos-verificados', [NormaController::class, 'gruposVerificados'])
            ->name('normas.grupos_verificados');

        // Remover verificação
        Route::delete('/grupos-verificados/{id}', [NormaController::class, 'removerVerificacao'])
        ->name('normas.remover_verificacao');
    });

    Route::group(['prefix' => 'normas', 'middleware' => ['auth', 'admin']], function(){
        // Criação
        Route::get('/norma_create', [NormaController::class, 'create'])->name('normas.norma_create');
        Route::post('/norma_store', [NormaController::class, 'store'])->name('normas.norma_store');

        // Edição
        Route::get('/norma_edit/{id}', [NormaController::class, 'edit'])->name('normas.norma_edit');
        Route::post('/norma_update/{id}', [NormaController::class, 'update'])->name('normas.norma_update');

        // Exclusão (soft delete)
        Route::delete('/norma_destroy/{id}', [NormaController::class, 'destroy'])->name('normas.norma_destroy');
    });

    // =====================  ÓRGÃOS   ============================
    Route::group(['prefix' => 'orgaos', 'middleware' => ['auth', 'admin']], function(){
        Route::get('/orgao_list', [OrgaoController::class, 'show'])->name('orgaos.orgao_list');
        Route::get('/orgao_create', [OrgaoController::class, 'create'])->name('orgaos.orgao_create');
        Route::post('/orgao_store', [OrgaoController::class, 'store'])->name('orgaos.orgao_store');
        Route::post('/orgao_update/{id}', [OrgaoController::class, 'update'])->name('orgaos.orgao_update');
        Route::get('/orgao_edit/{id}', [OrgaoController::class, 'edit'])->name('orgaos.orgao_edit');
        Route::get('/excluir/{id}', [OrgaoController::class, 'destroy'])->name('orgaos.excluir');
    });

    // =====================  ESPECIFICAÇÕES ADMINISTRATIVAS  ============================
    Route::group(['prefix' => 'especificacoes', 'middleware' => ['auth', 'admin']], function(){
        Route::get('/especificacoes_list', [EspecificacaoController::class, 'index'])->name('especificacoes.especificacoes_list');
        Route::get('/especificacoes_create', [EspecificacaoController::class, 'create'])->name('especificacoes.especificacoes_create');
        Route::post('/especificacoes_store', [EspecificacaoController::class, 'store'])->name('especificacoes.especificacoes_store');
        Route::get('/especificacoes_edit/{id}', [EspecificacaoController::class, 'edit'])->name('especificacoes.especificacoes_edit');
        Route::post('/especificacoes_update/{id}', [EspecificacaoController::class, 'update'])->name('especificacoes.especificacoes_update');

        // Excluir especificação (soft delete)
        Route::get('/excluir/{id}', [EspecificacaoController::class, 'destroy'])->name('especificacoes.excluir');

        // Download e visualização de arquivos (área administrativa)
        Route::get('/download/{id}', [EspecificacaoController::class, 'download'])->name('especificacoes.download');
        Route::get('/view/{id}', [EspecificacaoController::class, 'view'])->name('especificacoes.view');
    });

    // =====================  GESTÃO DE BOLETINS - APENAS ROLE 1 (ROOT) ou 7  ============================
    Route::group(['prefix' => 'admin/boletins', 'middleware' => ['auth']], function(){

        // Listagem
        Route::get('/', [BoletimController::class, 'index'])->name('boletins.index');

        // Formulário de criação
        Route::get('/create', [BoletimController::class, 'create'])->name('boletins.create');

        Route::post('/', [BoletimController::class, 'store'])->name('boletins.store');

        // Formulário de edição
        Route::get('/{id}/edit', [BoletimController::class, 'edit'])->name('boletins.edit');

        // Atualizar boletim
        Route::put('/{id}', [BoletimController::class, 'update'])->name('boletins.update');

        // Remover boletim - soft delete
        Route::delete('/{id}', [BoletimController::class, 'destroy'])->name('boletins.destroy');

        // Visualizar PDF
        Route::get('/{id}/view', [BoletimController::class, 'view'])->name('boletins.view');

        // Download PDF
        Route::get('/{id}/download', [BoletimController::class, 'download'])->name('boletins.download');

        Route::get('/indexacao', [BoletimController::class, 'indexacao'])
        ->name('boletins.indexacao');

    Route::post('/indexacao/iniciar', [BoletimController::class, 'iniciarIndexacao'])
        ->name('boletins.indexacao.iniciar');
        
    Route::get('/meu-nome', [BoletimController::class, 'meuNome'])
        ->name('boletins.meuNome');
    });

    // =====================  TIPOS   ============================
    Route::group(['prefix' => 'tipos', 'middleware' => ['auth', 'admin']], function(){
        Route::get('/tipo_list', [TipoController::class, 'show'])->name('tipos.tipo_list');
        Route::get('/tipo_create', [TipoController::class, 'create'])->name('tipos.tipo_create');
        Route::post('/tipo_store', [TipoController::class, 'store'])->name('tipos.tipo_store');
        Route::get('/tipo_edit/{id}', [TipoController::class, 'edit'])->name('tipos.tipo_edit');
        Route::post('/tipo_update/{id}', [TipoController::class, 'update'])->name('tipos.tipo_update');
        Route::get('/excluir/{id}', [TipoController::class, 'destroy'])->name('tipos.excluir');
    });

    // =====================  PALAVRAS CHAVES   ============================
    Route::group(['prefix' => 'palavras_chaves', 'middleware' => ['auth', 'admin']], function(){
        Route::get('/palavras_chaves_create', [PalavraChaveController::class, 'create'])->name('palavras_chaves.palavras_chaves_create');
        Route::post('/palavras_chaves_store', [PalavraChaveController::class, 'store'])->name('palavras_chaves.palavras_chaves_store');
        Route::get('/palavras_chaves_list', [PalavraChaveController::class, 'index'])->name('palavras_chaves.palavras_chaves_list');
        Route::get('/palavras_chaves_edit/{id}', [PalavraChaveController::class, 'edit'])->name('palavras_chaves.palavras_chaves_edit');
        Route::post('/palavras_chaves_update/{id}', [PalavraChaveController::class, 'update'])->name('palavras_chaves.palavras_chaves_update');

        // Desvincular palavra-chave de uma norma
        Route::get('/desvincular/{palavra_chave_id}/{norma_id}', [PalavraChaveController::class, 'desvincular'])
        ->name('palavras_chaves.desvincular');

        // Excluir palavra-chave permanentemente
        Route::get('/excluir/{id}', [PalavraChaveController::class, 'destroy'])
        ->name('palavras_chaves.excluir');

        // Obter todas as normas vinculadas a uma palavra-chave (para o modal)
        Route::get('/normas-vinculadas/{id}', [PalavraChaveController::class, 'normasVinculadas'])
        ->name('palavras_chaves.normas_vinculadas');

        Route::get('/estatisticas', [PalavraChaveController::class, 'estatisticas'])
        ->name('palavras_chaves.estatisticas');
    });

    // =====================  DASHBOARD DE VIGÊNCIA   ============================
    Route::group(['prefix' => 'vigencia', 'middleware' => ['auth']], function(){
        // Dashboard de vigência - apenas para role 1 e 2
        Route::get('/dashboard', [VigenciaDashboardController::class, 'index'])
            ->name('vigencia.dashboard');

        // Executar atualização de vigência
        Route::post('/executar', [VigenciaDashboardController::class, 'executarAtualizacao'])
            ->name('vigencia.executar');

        // Atualizar norma específica
        Route::post('/atualizar-norma/{id}', [VigenciaDashboardController::class, 'atualizarNormaEspecifica'])
            ->name('vigencia.atualizar.norma');

        // API para dados de gráficos
        Route::get('/dados-graficos', [VigenciaDashboardController::class, 'getDadosGraficos'])
            ->name('vigencia.dados.graficos');
    });
});
