<?php

namespace App\Http\Controllers;

use App\Models\Boletim;
use App\Http\Requests\CreateBoletimRequest;
use App\Http\Requests\UpdateBoletimRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageHelper;
use Illuminate\Support\Str;

class BoletimController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Middleware para verificar se é root (1) ou role 7
        $this->middleware(function ($request, $next) {
            if (!in_array(Auth::user()->role_id, [1, 7])) {
                abort(403, 'Acesso negado. Apenas usuários autorizados podem acessar esta área.');
            }
            return $next($request);
        });
    }

    /**
     * Lista todos os boletins
     */
    public function index()
    {
        try {
            $boletins = Boletim::with('usuario')
                ->ativos()
                ->ordenado()
                ->paginate(15);

            return view('boletins.boletim_list', compact('boletins'));

        } catch (\Exception $e) {
            Log::error('Erro ao listar boletins: ' . $e->getMessage());
            return back()->withErrors(['Erro ao carregar lista de boletins.']);
        }
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        return view('boletins.boletim_create');
    }

    /**
     * Armazena novo boletim
     */
    public function store(CreateBoletimRequest $request)
    {
        try {
            // Upload do arquivo para MinIO
            $arquivo = $request->file('arquivo');
            $nomeArquivo = $this->generateUniqueFileName($arquivo, $request->nome);
            
            // Salvar no bucket 'boletins'
            $uploaded = StorageHelper::boletins()->putFileAs('', $arquivo, $nomeArquivo);
            
            if (!$uploaded) {
                return back()->withErrors(['Erro ao fazer upload do arquivo.'])->withInput();
            }

            // Criar registro no banco
            Boletim::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'data_publicacao' => $request->data_publicacao,
                'arquivo' => $nomeArquivo,
                'user_id' => Auth::id()
            ]);

            return redirect()
                ->route('boletins.index')
                ->withSuccess('Boletim cadastrado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar boletim: ' . $e->getMessage());
            return back()
                ->withErrors(['Erro ao cadastrar boletim. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Exibe formulário de edição
     */
    public function edit($id)
    {
        try {
            $boletim = Boletim::findOrFail($id);
            return view('boletins.boletim_edit', compact('boletim'));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar boletim para edição: ' . $e->getMessage());
            return back()->withErrors(['Boletim não encontrado.']);
        }
    }

    /**
     * Atualiza boletim
     */
    public function update(UpdateBoletimRequest $request, $id)
    {
        try {
            $boletim = Boletim::findOrFail($id);
            $nomeArquivoAtual = $boletim->arquivo;

            // Se enviou novo arquivo
            if ($request->hasFile('arquivo')) {
                $arquivo = $request->file('arquivo');
                $novoNomeArquivo = $this->generateUniqueFileName($arquivo, $request->nome);
                
                // Upload do novo arquivo
                $uploaded = StorageHelper::boletins()->putFileAs('', $arquivo, $novoNomeArquivo);
                
                if ($uploaded) {
                    // Remove arquivo antigo se conseguiu fazer upload do novo
                    if (StorageHelper::boletins()->exists($nomeArquivoAtual)) {
                        StorageHelper::boletins()->delete($nomeArquivoAtual);
                    }
                    $nomeArquivoAtual = $novoNomeArquivo;
                } else {
                    return back()->withErrors(['Erro ao fazer upload do novo arquivo.'])->withInput();
                }
            }

            // Atualizar registro
            $boletim->update([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'data_publicacao' => $request->data_publicacao,
                'arquivo' => $nomeArquivoAtual
            ]);

            return redirect()
                ->route('boletins.index')
                ->withSuccess('Boletim atualizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar boletim: ' . $e->getMessage());
            return back()
                ->withErrors(['Erro ao atualizar boletim. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove boletim (soft delete)
     */
    public function destroy($id)
    {
        try {
            $boletim = Boletim::findOrFail($id);
            
            // Soft delete - apenas marca como inativo
            $boletim->update(['status' => false]);

            return redirect()
                ->route('boletins.index')
                ->withSuccess('Boletim removido com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao remover boletim: ' . $e->getMessage());
            return back()->withErrors(['Erro ao remover boletim.']);
        }
    }

    /**
     * Download do arquivo PDF
     */
    public function download($id)
    {
        try {
            $boletim = Boletim::findOrFail($id);
            
            if (!$boletim->arquivo) {
                return back()->withErrors(['Arquivo não encontrado.']);
            }

            if (!StorageHelper::boletins()->exists($boletim->arquivo)) {
                return back()->withErrors(['Arquivo não encontrado no servidor.']);
            }

            // Buscar arquivo do MinIO
            $conteudo = StorageHelper::boletins()->get($boletim->arquivo);
            
            return response($conteudo, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $boletim->nome_arquivo_download . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao fazer download do boletim: ' . $e->getMessage());
            return back()->withErrors(['Erro ao fazer download do arquivo.']);
        }
    }

    /**
     * Visualizar PDF no navegador
     */
    public function view($id)
    {
        try {
            $boletim = Boletim::findOrFail($id);
            
            if (!$boletim->arquivo) {
                abort(404, 'Arquivo não encontrado');
            }

            if (!StorageHelper::boletins()->exists($boletim->arquivo)) {
                abort(404, 'Arquivo não encontrado no servidor');
            }

            // Buscar arquivo do MinIO
            $conteudo = StorageHelper::boletins()->get($boletim->arquivo);

            return response($conteudo, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $boletim->nome . '.pdf"',
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao visualizar boletim: ' . $e->getMessage());
            abort(404, 'Arquivo não encontrado');
        }
    }

    /**
     * Gera nome único para o arquivo baseado no nome do boletim
     */
    private function generateUniqueFileName($file, $nomeBoletim)
    {
        $extension = $file->getClientOriginalExtension();
        $nomeBoletimSanitizado = $this->sanitize_filename($nomeBoletim);
        
        return $nomeBoletimSanitizado . '.' . $extension;
    }

    /**
     * Sanitiza nome do arquivo
     */
    private function sanitize_filename($filename)
    {
        $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return trim($filename, '_');
    }
}