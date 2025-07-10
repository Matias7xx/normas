<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePalavraChaveRequest;
use App\Models\Norma;
use App\Models\NormaChave;
use App\Models\PalavraChave;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PalavraChaveController extends Controller
{
    /**
     * Exibe a lista de palavras-chave com estatísticas
     */
    public function index(Request $request)
    {
        try {
            // Paginação (padrão 15)
            $perPage = $request->input('per_page', 15);
            $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;

            // Buscar palavras-chave com suas normas ativas
            $palavras_chave = PalavraChave::where('status', true)
                ->withCount(['normasAtivas'])
                ->with(['normasAtivas' => function($query) {
                    $query->select('normas.id', 'normas.descricao')
                        ->orderBy('normas.created_at', 'desc')
                        ->limit(3); // Carregar até 3 normas para exibição
                }])
                ->orderBy('palavra_chave')
                ->paginate($perPage);

            // Manter parâmetros na URL da paginação
            $palavras_chave->appends($request->only(['per_page']));

            foreach ($palavras_chave as $palavra) {
                if ($palavra->normas_ativas_count > 0 && $palavra->normasAtivas->count() === 0) {
                    Log::warning('Inconsistência detectada na palavra-chave', [
                        'id' => $palavra->id,
                        'palavra_chave' => $palavra->palavra_chave,
                        'count_esperado' => $palavra->normas_ativas_count,
                        'normas_carregadas' => $palavra->normasAtivas->count()
                    ]);
                    
                    // Recarregar esta palavra-chave específica
                    $palavra->load(['normasAtivas' => function($query) {
                        $query->select('normas.id', 'normas.descricao')
                            ->orderBy('normas.created_at', 'desc');
                    }]);
                }
            }
            
            Log::info('Lista de palavras-chave carregada', [
                'total' => $palavras_chave->total(),
                'por_pagina' => $palavras_chave->perPage(),
                'pagina_atual' => $palavras_chave->currentPage(),
                'com_normas' => $palavras_chave->where('normas_ativas_count', '>', 0)->count()
            ]);

            return view('palavras_chaves.palavras_chaves_list', compact('palavras_chave'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar palavras-chave: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            $palavras_chave = collect([])->paginate(15);
            return view('palavras_chaves.palavras_chaves_list', compact('palavras_chave'))
                ->withErrors(['Erro ao carregar palavras-chave: ' . $e->getMessage()]);
        }
    }

    /**
     * Exibe o formulário de criação
     */
    public function create()
    {
        return view('palavras_chaves.palavras_chaves_create');
    }

    /**
     * Armazena uma nova palavra-chave
     */
    public function store(CreatePalavraChaveRequest $request)
    {
        try {
            // Verificar se já existe palavra-chave similar
            $palavraExistente = PalavraChave::where('palavra_chave', 'LIKE', '%' . $request->palavra_chave . '%')
                ->where('status', true)
                ->first();
                
            if ($palavraExistente) {
                return back()
                    ->withInput()
                    ->withErrors(['A palavra-chave "' . $palavraExistente->palavra_chave . '" já existe no sistema.']);
            }

            $palavraChave = PalavraChave::create([
                'usuario_id' => auth()->user()->id,
                'palavra_chave' => trim($request->palavra_chave),
                'status' => true
            ]);
            
            // Limpar cache
            Cache::forget('palavras_chave_list');
            
            Log::info('Palavra-chave criada com sucesso', [
                'id' => $palavraChave->id,
                'palavra_chave' => $palavraChave->palavra_chave,
                'usuario' => auth()->user()->name
            ]);

            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withSuccess('Palavra-chave "' . $palavraChave->palavra_chave . '" cadastrada com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao criar palavra-chave: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['Erro interno no servidor. Tente novamente ou contate o administrador.']);
        }
    }

    /**
     * Atualiza uma palavra-chave existente
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'palavra_chave' => 'required|string|max:100|min:2'
            ]);
            
            $palavraChave = PalavraChave::findOrFail($id);
            $palavraChaveAntiga = $palavraChave->palavra_chave;
            
            // Verificar se nova palavra-chave já existe (exceto a atual)
            $palavraExistente = PalavraChave::where('palavra_chave', 'LIKE', '%' . $request->palavra_chave . '%')
                ->where('status', true)
                ->where('id', '!=', $id)
                ->first();
                
            if ($palavraExistente) {
                return back()
                    ->withInput()
                    ->withErrors(['A palavra-chave "' . $palavraExistente->palavra_chave . '" já existe no sistema.']);
            }
            
            $palavraChave->palavra_chave = trim($request->palavra_chave);
            $palavraChave->save();
            
            // Limpar cache
            Cache::forget('palavras_chave_list');
            
            Log::info('Palavra-chave atualizada com sucesso', [
                'id' => $palavraChave->id,
                'palavra_chave_antiga' => $palavraChaveAntiga,
                'palavra_chave_nova' => $palavraChave->palavra_chave,
                'usuario' => auth()->user()->name
            ]);
            
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withSuccess('Palavra-chave atualizada de "' . $palavraChaveAntiga . '" para "' . $palavraChave->palavra_chave . '" com sucesso!');
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withErrors(['Palavra-chave não encontrada.']);
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar palavra-chave: ' . $e->getMessage(), [
                'id' => $id,
                'request_data' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['Erro interno no servidor. Tente novamente ou contate o administrador.']);
        }
    }

    /**
     * Exibe o formulário de edição
     */
    public function edit($id)
    {
        try {
            $palavra_chave = PalavraChave::where('status', true)
                ->where('id', $id)
                ->firstOrFail();
                
            return view('palavras_chaves.palavras_chaves_edit', compact('palavra_chave'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withErrors(['Palavra-chave não encontrada.']);
        }
    }

    /**
     * Desvincula uma palavra-chave de uma norma específica
     */
    public function desvincular($palavraChaveId, $normaId)
    {
        try {
            DB::beginTransaction();
            
            // Validar IDs
            if (!is_numeric($palavraChaveId) || !is_numeric($normaId)) {
                throw new \InvalidArgumentException('IDs inválidos fornecidos.');
            }
            
            // Buscar a relação entre palavra-chave e norma
            $normaChave = NormaChave::where('palavra_chave_id', $palavraChaveId)
                ->where('norma_id', $normaId)
                ->where('status', true)
                ->first();
            
            if (!$normaChave) {
                DB::rollBack();
                return redirect()->route('palavras_chaves.palavras_chaves_list')
                    ->withErrors(['Vinculação não encontrada ou já foi desvinculada.']);
            }
            
            // informações para log
            $palavraChave = PalavraChave::find($palavraChaveId);
            $norma = Norma::find($normaId);
            
            // Realizar soft delete (desativação)
            $normaChave->status = false;
            $normaChave->save();
            
            // Limpar cache
            Cache::forget('palavras_chave_list');
            
            DB::commit();
            
            Log::info('Palavra-chave desvinculada com sucesso', [
                'palavra_chave_id' => $palavraChaveId,
                'palavra_chave' => $palavraChave->palavra_chave ?? 'N/A',
                'norma_id' => $normaId,
                'norma_descricao' => $norma->descricao ?? 'N/A',
                'usuario' => auth()->user()->name
            ]);
            
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withSuccess('Palavra-chave "' . ($palavraChave->palavra_chave ?? 'N/A') . '" desvinculada da norma com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desvincular palavra-chave: ' . $e->getMessage(), [
                'palavra_chave_id' => $palavraChaveId,
                'norma_id' => $normaId,
                'exception' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withErrors(['Erro ao desvincular: ' . $e->getMessage()]);
        }
    }

    /**
     * Retorna todas as normas vinculadas a uma palavra-chave via AJAX
     */
    public function normasVinculadas($id)
{
    try {
        // Validar ID
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('ID inválido fornecido.');
        }
        
        // Buscar a palavra-chave com suas normas ativas
        $palavraChave = PalavraChave::with(['normasAtivas' => function($query) {
            $query->select('normas.id', 'normas.descricao', 'normas.data')
                  ->orderBy('normas.data', 'desc');
        }])->findOrFail($id);
        
        // Converter as normas para array simples
        $normas = $palavraChave->normasAtivas->map(function($norma) {
            return [
                'id' => $norma->id,
                'descricao' => $norma->descricao,
                'data' => $norma->data
            ];
        });
        
        Log::info('Normas vinculadas carregadas via AJAX (Eloquent)', [
            'palavra_chave_id' => $id,
            'palavra_chave' => $palavraChave->palavra_chave,
            'total_normas' => $normas->count()
        ]);
        
        return response()->json([
            'success' => true,
            'palavra_chave' => $palavraChave->palavra_chave,
            'palavra_chave_id' => $palavraChave->id,
            'normas' => $normas,
            'total' => $normas->count()
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::warning('Palavra-chave não encontrada para AJAX', ['id' => $id]);
        return response()->json([
            'success' => false,
            'error' => 'Palavra-chave não encontrada.'
        ], 404);
        
    } catch (\Exception $e) {
        Log::error('Erro ao buscar normas vinculadas via AJAX (Eloquent): ' . $e->getMessage(), [
            'palavra_chave_id' => $id,
            'exception' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Erro interno do servidor. Tente novamente.',
            'debug' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

    /**
     * Remove permanentemente uma palavra-chave (apenas se não estiver vinculada a nenhuma norma)
     */
        public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Validar ID
            if (!is_numeric($id)) {
                throw new \InvalidArgumentException('ID inválido fornecido.');
            }
            
            // Buscar a palavra-chave
            $palavraChave = PalavraChave::findOrFail($id);
            
            // Buscar TODAS as vinculações (ativas e inativas)
            $todasVinculacoes = NormaChave::where('palavra_chave_id', $id)->get();
            
            // Verificar apenas vinculações ATIVAS com normas ATIVAS
            $vinculacoesAtivas = NormaChave::where('palavra_chave_id', $id)
                ->where('status', true)
                ->whereHas('norma', function($query) {
                    $query->where('status', true);
                })
                ->count();
            
            Log::info('Análise de vinculações para exclusão', [
                'palavra_chave_id' => $id,
                'palavra_chave' => $palavraChave->palavra_chave,
                'total_vinculacoes' => $todasVinculacoes->count(),
                'vinculacoes_ativas' => $vinculacoesAtivas,
                'usuario' => auth()->user()->name
            ]);
            
            if ($vinculacoesAtivas > 0) {
                DB::rollBack();
                Log::warning('Exclusão cancelada - existem vinculações ativas', [
                    'palavra_chave_id' => $id,
                    'vinculacoes_ativas' => $vinculacoesAtivas
                ]);
                
                return redirect()->route('palavras_chaves.palavras_chaves_list')
                    ->withErrors([
                        'Não é possível excluir a palavra-chave "' . $palavraChave->palavra_chave . 
                        '" porque ela ainda tem ' . $vinculacoesAtivas . ' norma(s) ativa(s) vinculada(s). ' .
                        'Primeiro desvincule todas as normas.'
                    ]);
            }
            
            // PASSO 1: Remover TODAS as vinculações (ativas e inativas) da tabela normas_chaves
            $vinculacoesRemovidas = NormaChave::where('palavra_chave_id', $id)->delete();
            
            Log::info('Vinculações removidas', [
                'palavra_chave_id' => $id,
                'vinculacoes_removidas' => $vinculacoesRemovidas
            ]);
            
            // PASSO 2: Remover a palavra-chave
            $palavraChaveNome = $palavraChave->palavra_chave;
            $palavraChave->delete();
            
            // Limpar cache
            Cache::forget('palavras_chave_list');
            
            DB::commit();
            
            Log::warning('Palavra-chave excluída permanentemente com limpeza completa', [
                'id' => $id,
                'palavra_chave' => $palavraChaveNome,
                'vinculacoes_removidas' => $vinculacoesRemovidas,
                'usuario' => auth()->user()->name
            ]);
            
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withSuccess('Palavra-chave "' . $palavraChaveNome . '" excluída permanentemente com sucesso! (' . $vinculacoesRemovidas . ' vinculações antigas removidas)');
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Tentativa de excluir palavra-chave inexistente', ['id' => $id]);
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withErrors(['Palavra-chave não encontrada.']);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir palavra-chave: ' . $e->getMessage(), [
                'id' => $id,
                'sql_state' => $e->getCode(),
                'exception' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withErrors(['Erro ao excluir palavra-chave: ' . $e->getMessage()]);
        }
    }

    /**
     * Retorna estatísticas das palavras-chave
     */
    public function estatisticas()
    {
        try {
            $stats = Cache::remember('palavras_chave_stats', 600, function () {
                $total = PalavraChave::where('status', true)->count();
                $comVinculos = PalavraChave::where('status', true)
                    ->whereHas('normasAtivas')
                    ->count();
                $semVinculos = $total - $comVinculos;
                
                $maisUsadas = PalavraChave::where('status', true)
                    ->withCount('normasAtivas')
                    ->orderByDesc('normas_ativas_count')
                    ->limit(5)
                    ->get();
                
                return [
                    'total' => $total,
                    'com_vinculos' => $comVinculos,
                    'sem_vinculos' => $semVinculos,
                    'mais_usadas' => $maisUsadas
                ];
            });
            
            return response()->json(['success' => true, 'data' => $stats]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar estatísticas: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}