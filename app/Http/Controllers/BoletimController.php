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
use Carbon\Carbon;

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
    public function index(Request $request)
    {
        try {
            $query = Boletim::with('usuario')->ativos();

            $temFiltros = $request->anyFilled(['search_term', 'data_publicacao', 'mes_ano']);
            $isBusca = $request->has('busca') || $temFiltros;

            if ($isBusca) {
                $boletins = $this->executarBuscaBoletins($request, $query);
            } else {
                // Mostrar boletins do mês atual por padrão
                $mesAtual = Carbon::now()->format('Y-m');
                $boletins = $this->buscarBoletinsPorMes($query, $mesAtual);
            }

            // Paginação
            if ($boletins instanceof \Illuminate\Database\Eloquent\Builder) {
                $boletins = $boletins->paginate(40);
                $boletins->appends($request->only(['search_term', 'data_publicacao', 'mes_ano', 'busca']));
            } elseif ($boletins instanceof \Illuminate\Support\Collection) {
                $currentPage = $request->input('page', 1);
                $perPage = 40;
                $pagedData = $boletins->slice(($currentPage - 1) * $perPage, $perPage)->values();
                
                $boletins = new \Illuminate\Pagination\LengthAwarePaginator(
                    $pagedData,
                    $boletins->count(),
                    $perPage,
                    $currentPage,
                    ['path' => request()->url(), 'pageName' => 'page']
                );
                
                $boletins->appends($request->only(['search_term', 'data_publicacao', 'mes_ano', 'busca']));
            }

            return view('boletins.boletim_list', [
                'boletins' => $boletins,
                'filtros' => $request->only(['search_term', 'data_publicacao', 'mes_ano']),
                'mostrandoMesAtual' => !$isBusca,
                'mesAtual' => Carbon::now()->format('Y-m'),
                'totalEncontrados' => $boletins->total(),
                'expandirFiltros' => $isBusca || $request->get('expandir_filtros', true)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar boletins: ' . $e->getMessage());
            return back()->withErrors(['Erro ao carregar lista de boletins.']);
        }
    }

    /**
     * Executar busca de boletins
     */
    private function executarBuscaBoletins(Request $request, $query)
    {
        // Filtro por termo de busca
        if ($request->filled('search_term')) {
            $searchTerm = trim($request->search_term);
            $query->where(function($q) use ($searchTerm) {
                $q->where('nome', 'ILIKE', "%{$searchTerm}%")
                ->orWhere('descricao', 'ILIKE', "%{$searchTerm}%");
            });
        }

        // Filtro por data exata
        if ($request->filled('data_publicacao')) {
            try {
                $dataFiltro = Carbon::parse($request->data_publicacao);
                
                // Verificar se não é data futura
                if ($dataFiltro->isFuture()) {
                    Log::warning('Tentativa de busca com data futura: ' . $request->data_publicacao);
                    // Retorna query que não vai encontrar nada
                    return $query->whereRaw('1 = 0');
                }
                
                $query->whereDate('data_publicacao', $dataFiltro->toDateString());
            } catch (\Exception $e) {
                Log::warning('Data inválida no filtro: ' . $request->data_publicacao);
                return $query->whereRaw('1 = 0');
            }
        }

        // Filtro por mês/ano - usando TO_CHAR
        if ($request->filled('mes_ano') && !$request->filled('data_publicacao')) {
            try {
                if (preg_match('/^(\d{4})-(\d{2})$/', $request->mes_ano, $matches)) {
                    $mesAnoFormatado = $matches[1] . '-' . $matches[2]; // YYYY-MM
                    
                    // Verificar se não é mês futuro
                    $mesAtual = Carbon::now()->format('Y-m');
                    if ($mesAnoFormatado > $mesAtual) {
                        Log::warning('Tentativa de busca com mês futuro: ' . $request->mes_ano);
                        return $query->whereRaw('1 = 0');
                    }
                    
                    // TO_CHAR(data_publicacao, 'YYYY-MM') = '2025-08'
                    $query->whereRaw("TO_CHAR(data_publicacao, 'YYYY-MM') = ?", [$mesAnoFormatado]);
                }
            } catch (\Exception $e) {
                Log::warning('Mês/ano inválido no filtro: ' . $request->mes_ano);
                return $query->whereRaw('1 = 0');
            }
        }

        // Retornar com ordenação
        return $query->orderBy('data_publicacao', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Buscar boletins por mês específico
     */
    private function buscarBoletinsPorMes($query, $mesAno)
    {
        try {
            if (preg_match('/^(\d{4})-(\d{2})$/', $mesAno, $matches)) {
                $mesAnoFormatado = $matches[1] . '-' . $matches[2];
                
                // Verificar se não é mês futuro
                $mesAtual = Carbon::now()->format('Y-m');
                if ($mesAnoFormatado > $mesAtual) {
                    return $query->whereRaw('1 = 0');
                }
                
                // TO_CHAR(data_publicacao, 'YYYY-MM') = '2025-08'
                return $query->whereRaw("TO_CHAR(data_publicacao, 'YYYY-MM') = ?", [$mesAnoFormatado])
                            ->orderBy('data_publicacao', 'desc')
                            ->orderBy('created_at', 'desc');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao buscar boletins do mês: ' . $e->getMessage());
        }

        // Fallback: retornar todos os boletins do mês atual
        $mesAtual = Carbon::now()->format('Y-m');
        return $query->whereRaw("TO_CHAR(data_publicacao, 'YYYY-MM') = ?", [$mesAtual])
                    ->orderBy('data_publicacao', 'desc')
                    ->orderBy('created_at', 'desc');
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