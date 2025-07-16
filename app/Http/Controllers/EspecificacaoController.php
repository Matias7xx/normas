<?php

namespace App\Http\Controllers;

use App\Models\Especificacao;
use App\Http\Requests\CreateEspecificacaoRequest;
use App\Http\Requests\UpdateEspecificacaoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\StorageHelper;

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
            $especificacoes = Especificacao::where('status', true)->paginate($perPage);
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

            // Upload do arquivo para MinIO
            if ($request->hasFile('arquivo')) {
                $arquivo = $request->file('arquivo');
                
                // Gerar nome único com UUID
                $nomeArquivo = Str::uuid() . '.' . $arquivo->getClientOriginalExtension();
                
                // Salvar no bucket 'especificacoes' via Helper
                StorageHelper::especificacoes()->putFileAs('/', $arquivo, $nomeArquivo);
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
                // Remover arquivo antigo do MinIO
                if ($especificacao->arquivo && StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
                    StorageHelper::especificacoes()->delete($especificacao->arquivo);
                }

                // Salvar novo arquivo no MinIO
                $arquivo = $request->file('arquivo');
                $nomeArquivo = Str::uuid() . '.' . $arquivo->getClientOriginalExtension();
                StorageHelper::especificacoes()->putFileAs('/', $arquivo, $nomeArquivo);
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
     * Download do arquivo PDF do MinIO
     */
    public function download($id)
    {
        try {
            $especificacao = Especificacao::findOrFail($id);
            
            if (!$especificacao->arquivo) {
                return back()->withErrors(['Arquivo não encontrado.']);
            }

            // BUSCAR NO BUCKET 'especificacoes
            if (!StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
                return back()->withErrors(['Arquivo não encontrado no servidor.']);
            }

            // Buscar arquivo do MinIO
            $conteudo = StorageHelper::especificacoes()->get($especificacao->arquivo);
            
            // Gerar nome para download
            $nomeDownload = $this->sanitize_filename($especificacao->nome) . '.pdf';

            return response($conteudo, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $nomeDownload . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

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

            // BUSCAR NO BUCKET 'especificacoes'
            if (!StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
                abort(404, 'Arquivo não encontrado no servidor');
            }

            // Buscar arquivo do MinIO
            $conteudo = StorageHelper::especificacoes()->get($especificacao->arquivo);

            return response($conteudo, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $especificacao->nome . '.pdf"',
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao visualizar especificação: ' . $e->getMessage());
            abort(404, 'Arquivo não encontrado');
        }
    }

    /**
     * Função para sanitizar nome de arquivo
     */
    private function sanitize_filename($filename) {
        // Remove caracteres especiais e substitui espaços por underscores
        $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return trim($filename, '_');
    }
}