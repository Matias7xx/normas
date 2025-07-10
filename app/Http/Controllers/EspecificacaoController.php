<?php

namespace App\Http\Controllers;

use App\Models\Especificacao;
use App\Http\Requests\CreateEspecificacaoRequest;
use App\Http\Requests\UpdateEspecificacaoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EspecificacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Paginação (padrão 15)
            $perPage = $request->input('per_page', 15);
            $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;

            $especificacoes = Especificacao::where('status', true)
                ->with('usuario')
                ->orderBy('nome')
                ->paginate($perPage);

            // Manter parâmetros na URL da paginação
            $especificacoes->appends($request->only(['per_page']));

            return view('especificacoes.especificacoes_list', compact('especificacoes'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar especificações: ' . $e->getMessage());
            $especificacoes = collect([])->paginate(15);
            return view('especificacoes.especificacoes_list', compact('especificacoes'))
                ->withErrors(['Erro ao carregar especificações: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('especificacoes.especificacoes_create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEspecificacaoRequest $request)
    {
        try {
            $nomeArquivo = null;

            // Upload do arquivo
            if ($request->hasFile('arquivo')) {
                $arquivo = $request->file('arquivo');
                $nomeArquivo = time() . '_' . $arquivo->getClientOriginalName();
                $arquivo->storeAs('especificacoes', $nomeArquivo, 'public');
            }

            Especificacao::create([
                'nome' => $request->nome,
                'arquivo' => $nomeArquivo,
                'status' => true,
                'usuario_id' => auth()->user()->id
            ]);

            return redirect()->route('especificacoes.especificacoes_list')
                ->withSuccess('Especificação cadastrada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao salvar especificação: ' . $e->getMessage());
            return back()->withInput()
                ->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $especificacao = Especificacao::findOrFail($id);
            return view('especificacoes.especificacoes_edit', compact('especificacao'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar especificação para edição: ' . $e->getMessage());
            return redirect()->route('especificacoes.especificacoes_list')
                ->withErrors(['Especificação não encontrada.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEspecificacaoRequest $request, $id)
    {
        try {
            $especificacao = Especificacao::findOrFail($id);
            $nomeArquivo = $especificacao->arquivo;

            // Upload do novo arquivo (se fornecido)
            if ($request->hasFile('arquivo')) {
                // Remover arquivo antigo
                if ($especificacao->arquivo && Storage::disk('public')->exists('especificacoes/' . $especificacao->arquivo)) {
                    Storage::disk('public')->delete('especificacoes/' . $especificacao->arquivo);
                }

                // Salvar novo arquivo
                $arquivo = $request->file('arquivo');
                $nomeArquivo = time() . '_' . $arquivo->getClientOriginalName();
                $arquivo->storeAs('especificacoes', $nomeArquivo, 'public');
            }

            $especificacao->update([
                'nome' => $request->nome,
                'arquivo' => $nomeArquivo
            ]);

            return redirect()->route('especificacoes.especificacoes_list')
                ->withSuccess('Especificação atualizada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar especificação: ' . $e->getMessage());
            return back()->withInput()
                ->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy($id)
    {
        try {
            $especificacao = Especificacao::findOrFail($id);
            
            // Soft delete - apenas marca como inativo
            $especificacao->update(['status' => false]);

            return redirect()->route('especificacoes.especificacoes_list')
                ->withSuccess('Especificação excluída com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir especificação: ' . $e->getMessage());
            return back()->withErrors(['Erro ao excluir especificação: ' . $e->getMessage()]);
        }
    }

    /**
     * Download do arquivo PDF
     */
    public function download($id)
    {
        try {
            $especificacao = Especificacao::findOrFail($id);
            
            if (!$especificacao->arquivo) {
                return back()->withErrors(['Arquivo não encontrado.']);
            }

            $filePath = storage_path('app/public/especificacoes/' . $especificacao->arquivo);
            
            if (!file_exists($filePath)) {
                return back()->withErrors(['Arquivo não encontrado no servidor.']);
            }

            return response()->download($filePath, $especificacao->nome . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao fazer download da especificação: ' . $e->getMessage());
            return back()->withErrors(['Erro ao fazer download do arquivo.']);
        }
    }

    /**
     * Visualizar PDF no navegador
     */
    public function view($id)
    {
        try {
            $especificacao = Especificacao::findOrFail($id);
            
            if (!$especificacao->arquivo) {
                abort(404, 'Arquivo não encontrado');
            }

            $filePath = storage_path('app/public/especificacoes/' . $especificacao->arquivo);
            
            if (!file_exists($filePath)) {
                abort(404, 'Arquivo não encontrado no servidor');
            }

            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $especificacao->nome . '.pdf"'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao visualizar especificação: ' . $e->getMessage());
            abort(404, 'Arquivo não encontrado');
        }
    }
}