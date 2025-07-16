<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Norma;
use App\Models\Tipo;
use App\Models\Orgao;
use App\Models\User;
use App\Models\Especificacao;
use Carbon\Carbon;
use App\Helpers\StorageHelper;

class PublicController extends Controller
{
    /**
     * Página inicial da SPA
     */
    public function home()
    {
        $stats = $this->getSystemStats();
        
        return Inertia::render('Home', [
            'stats' => $stats,
            'page' => 'home'
        ]);
    }

    /**
     * Página de consulta de normas
     */
    public function consulta(Request $request)
    {
        $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
        $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
        
        $filtros = $request->only([
            'search_term', 'tipo_id', 'orgao_id', 'vigente', 
            'data_inicio', 'data_fim', 'page'
        ]);

        $normas = null;
        
        // Verificar se é uma busca ativa (quando clica no botão buscar)
        $isBusca = $request->has('busca') || $request->anyFilled(['search_term', 'tipo_id', 'orgao_id', 'vigente', 'data_inicio', 'data_fim']);
        
        // Se há parâmetros de busca OU se clicou em buscar, realizar a consulta
        if ($isBusca) {
            $normas = $this->performSearch($request);
        }

        return Inertia::render('Consulta', [
            'tipos' => $tipos,
            'orgaos' => $orgaos,
            'normas' => $normas,
            'filtros' => $filtros,
            'stats' => $this->getSystemStats(),
            'page' => 'consulta'
        ]);
    }

    /**
     * Visualização de uma norma específica
     */
    public function normaView($id)
    {
        $norma = Norma::with(['tipo', 'orgao', 'palavrasChave'])
            ->where('status', true)
            ->findOrFail($id);

        // Buscar normas relacionadas
        $relacionadas = Norma::where('status', true)
            ->where('id', '!=', $id)
            ->where(function($query) use ($norma) {
                $query->where('tipo_id', $norma->tipo_id)
                      ->orWhere('orgao_id', $norma->orgao_id);
            })
            ->with(['tipo', 'orgao'])
            ->limit(5)
            ->get();

        return Inertia::render('NormaView', [
            'norma' => $norma,
            'relacionadas' => $relacionadas,
            'stats' => $this->getSystemStats(),
            'page' => 'norma'
        ]);
    }

    /**
     * Visualização de PDF da norma (para iframe)
     */
    public function viewNorma($id)
    {
        $norma = Norma::where('status', true)->findOrFail($id);
        
        if (!$norma->anexo) {
            abort(404, 'Arquivo não encontrado');
        }

        // BUSCAR NO BUCKET 'normas'
        if (!StorageHelper::normas()->exists($norma->anexo)) {
            abort(404, 'Arquivo PDF não encontrado: ' . $norma->anexo);
        }

        // Servir do bucket 'normas'
        $conteudo = StorageHelper::normas()->get($norma->anexo);
        
        return response($conteudo, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $norma->anexo . '"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Download de arquivo da norma
     */
    public function downloadNorma($id)
    {
        $norma = Norma::where('status', true)->findOrFail($id);
        
        if (!$norma->anexo) {
            abort(404, 'Arquivo não encontrado');
        }

        // BUSCAR NO BUCKET 'normas'
        if (!StorageHelper::normas()->exists($norma->anexo)) {
            abort(404, 'Arquivo não encontrado no servidor: ' . $norma->anexo);
        }

        // Gerar nome do arquivo para download
        $fileName = $norma->numero_norma 
            ? sanitize_filename($norma->numero_norma) . '.pdf'
            : sanitize_filename($norma->descricao) . '.pdf';

        // bucket 'normas'
        $conteudo = StorageHelper::normas()->get($norma->anexo);
        
        return response($conteudo, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * API para busca de normas (AJAX)
     */
    public function searchApi(Request $request)
    {
        $normas = $this->performSearch($request);
        
        return response()->json([
            'success' => true,
            'data' => $normas
        ]);
    }

    /**
     * API para obter estatísticas
     */
    public function getStats()
    {
        return response()->json($this->getSystemStats());
    }

    /**
     * API para obter tipos
     */
    public function getTipos()
    {
        $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
        return response()->json($tipos);
    }

    /**
     * API para obter órgãos
     */
    public function getOrgaos()
    {
        $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
        return response()->json($orgaos);
    }

    //Realizar busca de normas com sistema de relevância
    private function performSearch(Request $request)
    {
        // Base query com eager loading
        $query = Norma::with([
            'tipo:id,tipo', 
            'orgao:id,orgao', 
            'palavrasChave:id,palavra_chave'
        ])
        ->where('status', true);

        // Verificar se há algum filtro ativo
        $hasFilters = $request->anyFilled(['search_term', 'tipo_id', 'orgao_id', 'vigente', 'data_inicio', 'data_fim']);
        
        // Se não há filtros, mostrar todas as normas ativas ordenadas por data
        if (!$hasFilters) {
            $query->orderBy('data', 'desc')->orderBy('id', 'desc');
        } else {
            // Aplicar filtros de pesquisa com relevância apenas se há termo de busca
            if ($request->filled('search_term')) {
                $searchTerm = trim($request->search_term);
                $query = $this->applySearchWithRelevance($query, $searchTerm);
            } else {
                // Se há outros filtros mas não termo de busca, aplicar ordenação padrão
                $query->orderBy('data', 'desc')->orderBy('id', 'desc');
            }
        }

        // Filtro por tipo
        if ($request->filled('tipo_id')) {
            $query->where('tipo_id', $request->tipo_id);
        }

        // Filtro por órgão
        if ($request->filled('orgao_id')) {
            $query->where('orgao_id', $request->orgao_id);
        }

        // Filtro por vigência
        if ($request->filled('vigente')) {
            $query->where('vigente', $request->vigente);
        }

        // Filtro por data
        if ($request->filled('data_inicio')) {
            try {
                $dataInicio = Carbon::createFromFormat('Y-m-d', $request->data_inicio)->startOfDay();
                $query->where('data', '>=', $dataInicio);
            } catch (\Exception $e) {
                // Se erro na conversão de data, ignorar filtro
            }
        }

        if ($request->filled('data_fim')) {
            try {
                $dataFim = Carbon::createFromFormat('Y-m-d', $request->data_fim)->endOfDay();
                $query->where('data', '<=', $dataFim);
            } catch (\Exception $e) {
                // Se erro na conversão de data, ignorar filtro
            }
        }

        // Paginação
        $normas = $query->paginate(10);

        // Processar palavras-chave para exibição
        $normas->getCollection()->transform(function ($norma) {
            $palavrasChave = $norma->palavrasChave;
            
            if ($palavrasChave->count() > 3) {
                $norma->palavras_chave = $palavrasChave->take(3);
                $norma->palavras_chave_restantes = $palavrasChave->count() - 3;
            } else {
                $norma->palavras_chave = $palavrasChave;
                $norma->palavras_chave_restantes = 0;
            }
            
            return $norma;
        });

        return $normas;
    }

    //Aplicar busca com sistema de relevância
    private function applySearchWithRelevance($query, $searchTerm)
    {
        $searchTerm = trim($searchTerm);
        
        if (empty($searchTerm)) {
            return $query;
        }
        
        // Dividir em palavras e filtrar palavras muito pequenas
        $words = array_filter(array_map('trim', explode(' ', $searchTerm)), function($word) {
            return strlen($word) >= 2;
        });
        
        if (empty($words)) {
            return $query;
        }
        
        // Criar subquery para calcular relevância
        $relevanceSelect = $this->buildRelevanceScore($words, $searchTerm);
        
        return $query->select('normas.*')
                    ->selectRaw("({$relevanceSelect}) as relevance_score")
                    ->where(function($q) use ($words, $searchTerm) {
                        // Busca nos campos principais
                        foreach ($words as $word) {
                            $q->where(function($wordQuery) use ($word) {
                                $wordQuery->where('descricao', 'ILIKE', "%{$word}%")
                                         ->orWhere('resumo', 'ILIKE', "%{$word}%");
                            });
                        }
                        
                        // Busca nas palavras-chave
                        $q->orWhereHas('palavrasChave', function($subq) use ($words) {
                            foreach ($words as $word) {
                                $subq->where('palavra_chave', 'ILIKE', "%{$word}%");
                            }
                        });
                    })
                    ->orderByRaw('relevance_score DESC')
                    ->orderBy('data', 'desc');
    }

    /**
     * Constrói a query de score de relevância
     */
    private function buildRelevanceScore($words, $fullTerm)
    {
        $scoreQueries = [];
        
        // Score para frase exata (busca primeiro)
        $scoreQueries[] = "CASE WHEN descricao ILIKE '%{$fullTerm}%' THEN 10 ELSE 0 END";
        $scoreQueries[] = "CASE WHEN resumo ILIKE '%{$fullTerm}%' THEN 8 ELSE 0 END";
        
        // Score para palavras individuais
        foreach ($words as $index => $word) {
            $weight = max(1, 5 - $index); // Primeiras palavras têm peso maior
            $scoreQueries[] = "CASE WHEN descricao ILIKE '%{$word}%' THEN {$weight} ELSE 0 END";
            $scoreQueries[] = "CASE WHEN resumo ILIKE '%{$word}%' THEN " . ($weight - 1) . " ELSE 0 END";
        }
        
        return implode(' + ', $scoreQueries);
    }

    /**
     * Obter estatísticas do sistema
     */
    private function getSystemStats()
    {
        return [
            'total_normas' => Norma::where('status', true)->count(),
            'normas_vigentes' => Norma::where('status', true)
                ->where('vigente', 'VIGENTE')->count(),
            'tipos_count' => Tipo::where('status', true)->count(),
            'orgaos_count' => Orgao::where('status', true)->count(),
            'usuarios_ativos' => User::where('active', true)->count(),
            'normas_cadastradas' => Norma::where('status', true)->count(),
            'em_analise' => Norma::where('status', true)
                ->where('vigente', 'EM ANÁLISE')->count(),
            'nao_vigentes' => Norma::where('status', true)
                ->where('vigente', 'NÃO VIGENTE')->count(),
        ];
    }

    public function especificacoes()
{
    try {
        // Buscar especificações ativas com dados do usuário
        $especificacoes = Especificacao::where('status', true)
            ->with('usuario:id,name')
            ->orderBy('nome')
            ->get()
            ->map(function ($especificacao) {
                return [
                    'id' => $especificacao->id,
                    'nome' => $especificacao->nome,
                    'arquivo' => $especificacao->arquivo,
                    'created_at' => $especificacao->created_at,
                    'updated_at' => $especificacao->updated_at,
                    'usuario' => $especificacao->usuario ? [
                        'id' => $especificacao->usuario->id,
                        'name' => $especificacao->usuario->name
                    ] : null
                ];
            });

        return Inertia::render('Especificacoes', [
            'especificacoes' => $especificacoes,
            'stats' => $this->getSystemStats()
        ]);

    } catch (\Exception $e) {
        
        return Inertia::render('Especificacoes', [
            'especificacoes' => [],
            'error' => 'Erro ao carregar especificações técnicas'
        ]);
    }
}

/**
 * Download público de especificação
 */
public function downloadEspecificacao($id)
{
    try {
        $especificacao = Especificacao::where('status', true)
            ->findOrFail($id);
        
        if (!$especificacao->arquivo) {
            abort(404, 'Arquivo não encontrado');
        }

        // BUSCAR NO BUCKET 'especificacoes'
        if (!StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
            abort(404, 'Arquivo não encontrado no servidor: ' . $especificacao->arquivo);
        }

        // Buscar arquivo do MinIO
        $conteudo = StorageHelper::especificacoes()->get($especificacao->arquivo);
        
        // Gerar nome para download
        $nomeDownload = sanitize_filename($especificacao->nome) . '.pdf';

        return response($conteudo, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nomeDownload . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);

    } catch (\Exception $e) {
        Log::error('Erro ao fazer download da especificação: ' . $e->getMessage());
        abort(404, 'Arquivo não encontrado');
    }
}

/**
 * Visualizar PDF público de especificação
 */
public function viewEspecificacao($id)
{
    try {
        $especificacao = Especificacao::where('status', true)
            ->findOrFail($id);
        
        if (!$especificacao->arquivo) {
            abort(404, 'Arquivo não encontrado');
        }

        // BUSCAR NO BUCKET 'especificacoes'
        if (!StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
            abort(404, 'Arquivo não encontrado no servidor: ' . $especificacao->arquivo);
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
}

/**
 * Função auxiliar para sanitizar nome de arquivo
 */
if (!function_exists('sanitize_filename')) {
    function sanitize_filename($filename) {
        // Remove caracteres especiais e substitui espaços por underscores
        $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return trim($filename, '_');
    }
}