<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NormaController;
use App\Http\Controllers\NormaSearchPublicController;
use App\Http\Controllers\OrgaoController;
use App\Http\Controllers\PalavraChaveController;
use App\Http\Controllers\TipoController;
use App\Http\Middleware\Authenticate;
use App\Models\Orgao;
use App\Models\PalavraChave;
use App\Models\Tipo;
use Illuminate\Support\Facades\Route;

Route::get('/norma_public_search', [NormaSearchPublicController::class, 'search'])->name('norma_public_search');

Route::middleware([Authenticate::class])->group(function() {

    // Route::get('/', [HomeController::class, 'home'])->name('home_2');
    // Route::get('/home', [HomeController::class, 'home'])->name('home');

    Route::get('/', [NormaController::class, 'index'])->name('home');
    Route::get('/home', [NormaController::class, 'index'])->name('home');

    // =====================  USERS   ============================
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
    });

    //Rotas para o módulo de normas
Route::group(['prefix' => 'normas', 'middleware' => ['auth']], function(){
    //Listagem e pesquisa
    //Carregar as normas com Ajax
    Route::get('/ajax', [NormaController::class, 'getNormasAjax'])->name('normas.ajax');
    Route::get('/norma_list', [NormaController::class, 'index'])->name('normas.norma_list');
    /* Route::get('/norma_search', [NormaController::class, 'search'])->name('normas.norma_search'); */
    });
    
    Route::group(['prefix' => 'normas', 'middleware' => ['auth', 'admin']], function(){
        //Criação
        Route::get('/norma_create', [NormaController::class, 'create'])->name('normas.norma_create');
        Route::post('/norma_store', [NormaController::class, 'store'])->name('normas.norma_store');
        
        //Edição
        Route::get('/norma_edit/{id}', [NormaController::class, 'edit'])->name('normas.norma_edit');
        Route::post('/norma_update/{id}', [NormaController::class, 'update'])->name('normas.norma_update');
        
        //Exclusão (soft delete)
        Route::delete('/norma_destroy/{id}', [NormaController::class, 'destroy'])->name('normas.norma_destroy');
});

    // =====================  ORGAOS   ============================
    Route::group(['prefix' => 'orgaos', 'middleware' => ['auth', 'admin']], function(){
        Route::get('/orgao_list', [OrgaoController::class, 'show'])->name('orgaos.orgao_list');
        Route::get('/orgao_create', [OrgaoController::class, 'create'])->name('orgaos.orgao_create');
        Route::post('/orgao_store', [OrgaoController::class, 'store'])->name('orgaos.orgao_store');
        Route::post('/orgao_update/{id}', [OrgaoController::class, 'update'])->name('orgaos.orgao_update');
        Route::get('/orgao_edit/{id}', [OrgaoController::class, 'edit'])->name('orgaos.orgao_edit');

    });

    // =====================  TIPOS   ============================
    Route::group(['prefix' => 'tipos', 'middleware' => ['auth', 'admin']], function(){
        Route::get('/tipo_list', [TipoController::class, 'show'])->name('tipos.tipo_list');
        Route::get('/tipo_create', [TipoController::class, 'create'])->name('tipos.tipo_create');
        Route::post('/tipo_store', [TipoController::class, 'store'])->name('tipos.tipo_store');
        Route::get('/tipo_edit/{id}', [TipoController::class, 'edit'])->name('tipos.tipo_edit');
        Route::post('/tipo_update/{id}', [TipoController::class, 'update'])->name('tipos.tipo_update');

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
    });
});
