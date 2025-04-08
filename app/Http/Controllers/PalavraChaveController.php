<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePalavraChaveRequest;
use App\Models\Norma;
use App\Models\NormaChave;
use App\Models\PalavraChave;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PalavraChaveController extends Controller
{
    public function index()
    {
        try {
            // Obter palavras-chave com relações de normas
            $palavras_chave = PalavraChave::where('status', true)
                ->withCount(['normasAtivas']) // Conta quantas normas ativas estão associadas
                ->with(['normasAtivas' => function($query) {
                    $query->select('normas.id', 'normas.descricao');
                }])
                ->orderBy('palavra_chave')
                ->get();
                
            // Registrar informações para depuração
            Log::info('Palavras-chave carregadas: ' . $palavras_chave->count());
            foreach ($palavras_chave as $pc) {
                Log::info("Palavra-chave {$pc->id} ({$pc->palavra_chave}): {$pc->normas_ativas_count} normas vinculadas, carregadas: " . $pc->normasAtivas->count());
            }

            return view('palavras_chaves.palavras_chaves_list', compact('palavras_chave'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar palavras-chave: ' . $e->getMessage());
            // Retornar a view mesmo com erro, para não quebrar a página
            $palavras_chave = collect([]);
            return view('palavras_chaves.palavras_chaves_list', compact('palavras_chave'))
                ->withErrors(['Erro ao carregar palavras-chave: ' . $e->getMessage()]);
        }
    }

    public function create()
    {
        return view('palavras_chaves.palavras_chaves_create');
    }

    public function store(CreatePalavraChaveRequest $request)
    {
        try {
            PalavraChave::create([
                'usuario_id' => auth()->user()->id,
                'palavra_chave' => $request->palavra_chave,
                'status' => true
            ]);

            return redirect()->route('palavras_chaves.palavras_chaves_list')->withSuccess('Cadastro realizado com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $update_palavra_chave = PalavraChave::find($id);
            $update_palavra_chave->palavra_chave = $request->palavra_chave;
            $update_palavra_chave->save();
            return redirect()->route('palavras_chaves.palavras_chaves_list')->withSuccess('Edição realizada com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    public function edit($id)
    {
        $palavra_chave = PalavraChave::where('status', true)
            ->where('id', $id)
            ->orderBy('palavra_chave')
            ->first();
        return view('palavras_chaves.palavras_chaves_edit', compact(['palavra_chave']));
    }

    /**
     * Desvincula uma palavra-chave de uma norma específica
     *
     * @param int $palavraChaveId
     * @param int $normaId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function desvincular($palavraChaveId, $normaId)
    {
        try {
            DB::beginTransaction();
            
            // Buscar a relação entre palavra-chave e norma
            $normaChave = NormaChave::where('palavra_chave_id', $palavraChaveId)
                ->where('norma_id', $normaId)
                ->where('status', true)
                ->first();
            
            if (!$normaChave) {
                return redirect()->route('palavras_chaves.palavras_chaves_list')
                    ->withErrors(['Vinculação não encontrada ou já desvinculada.']);
            }
            
            // Realizar soft delete (desativação)
            $normaChave->status = false;
            $normaChave->save();
            
            DB::commit();
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withSuccess('Palavra-chave desvinculada da norma com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desvincular palavra-chave: ' . $e->getMessage());
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withErrors(['Erro ao desvincular: ' . $e->getMessage()]);
        }
    }

    /**
     * Retorna todas as normas vinculadas a uma palavra-chave via AJAX
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function normasVinculadas($id)
    {
        try {
            // Buscar a palavra-chave
            $palavraChave = PalavraChave::findOrFail($id);
            
            // Buscar explicitamente as normas usando a relação normas_chaves
            $normas = DB::table('normas')
                ->join('normas_chaves', 'normas.id', '=', 'normas_chaves.norma_id')
                ->where('normas_chaves.palavra_chave_id', $id)
                ->where('normas_chaves.status', true)
                ->where('normas.status', true)
                ->select('normas.id', 'normas.descricao')
                ->get();
            
            // Registrar para depuração
            Log::info("Normas vinculadas à palavra-chave {$id} ({$palavraChave->palavra_chave}): " . $normas->count());
            
            return response()->json([
                'palavra_chave' => $palavraChave->palavra_chave,
                'normas' => $normas
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar normas vinculadas: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); // Log da stack trace completa
            return response()->json([
                'error' => 'Erro ao buscar normas vinculadas: ' . $e->getMessage()
            ], 500);
        }
    }

        /**
     * Apaga permanentemente uma palavra-chave (apenas se não estiver vinculada a nenhuma norma)
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Buscar a palavra-chave
            $palavraChave = PalavraChave::findOrFail($id);
            
            // Verificar se há normas vinculadas ATIVAS
            $normasVinculadasCount = NormaChave::where('palavra_chave_id', $id)
                ->where('status', true)
                ->count();
            
            $existemNormasVinculadas = $normasVinculadasCount > 0;
            
            // Log para depuração
            Log::info("Tentativa de exclusão da palavra-chave ID {$id}: '{$palavraChave->palavra_chave}'");
            Log::info("Contagem de normas vinculadas ativas: {$normasVinculadasCount}");
            Log::info("Existem normas vinculadas? " . ($existemNormasVinculadas ? 'SIM' : 'NÃO'));
            
            if ($existemNormasVinculadas) {
                DB::rollBack();
                Log::warning("Exclusão cancelada: a palavra-chave ID {$id} ainda tem {$normasVinculadasCount} normas vinculadas");
                return redirect()->route('palavras_chaves.palavras_chaves_list')
                    ->withErrors(['Não é possível excluir esta palavra-chave porque ela está vinculada a uma ou mais normas.']);
            }
            
            // Se não houver normas vinculadas, pode excluir
            $palavraChave->delete();
            
            DB::commit();
            Log::info("Palavra-chave ID {$id} excluída com sucesso");
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withSuccess('Palavra-chave excluída permanentemente com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao excluir palavra-chave ID {$id}: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->route('palavras_chaves.palavras_chaves_list')
                ->withErrors(['Erro ao excluir: ' . $e->getMessage()]);
        }
    }
}