<?php

namespace App\Http\Controllers;

use App\Models\Boletim;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Norma;
use App\Models\Tipo;
use App\Models\Orgao;
use App\Models\User;
use App\Models\Especificacao;
use Carbon\Carbon;
use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
  /**
   * Página pública de boletins
   */
  public function boletins(Request $request)
  {
    try {
      $query = Boletim::where('status', true)->with(['usuario:id,name']);

      $temFiltros = $request->anyFilled([
        'search_term',
        'data_publicacao',
        'mes_ano',
        'meu_nome',
      ]);
      $isBusca = $request->has('busca') || $temFiltros;

      if ($request->filled('meu_nome') && $request->meu_nome == 1) {
        $boletins = $this->buscarPorMeuNome($request, $query);
        $mostrandoMesAtual = false;
      } elseif ($isBusca) {
        $boletins = $this->executarBuscaBoletins($request, $query);
        $mostrandoMesAtual = false;
      } else {
        $mesAtual = Carbon::now()->format('Y-m');
        $boletins = $this->buscarBoletinsPorMes($query, $mesAtual);
        $mostrandoMesAtual = true;
      }

      $boletins = $boletins->paginate(30);

      // Manter parâmetros na paginação
      $boletins->appends(
        $request->only([
          'search_term',
          'data_publicacao',
          'mes_ano',
          'busca',
          'meu_nome',
        ]),
      );

      return Inertia::render('Boletins', [
        'boletins' => $boletins,
        'filtros' => $request->only([
          'search_term',
          'data_publicacao',
          'mes_ano',
          'meu_nome',
        ]),
        'stats' => $this->getSystemStats(),
        'mostrandoMesAtual' => $mostrandoMesAtual,
        'mesAtual' => Carbon::now()->format('Y-m'),
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao carregar boletins: ' . $e->getMessage());

      return Inertia::render('Boletins', [
        'boletins' => Boletim::where('id', 0)->paginate(40), // Paginação vazia
        'filtros' => [],
        'error' => 'Erro ao carregar boletins',
        'stats' => $this->getSystemStats(),
        'mostrandoMesAtual' => false,
      ]);
    }
  }

  /**
   * Executar busca de boletins com filtros
   */
  private function executarBuscaBoletins(Request $request, $query)
  {
    // Filtro por termo de busca
    if ($request->filled('search_term')) {
      $searchTerm = trim($request->search_term);
      $query->where(function ($q) use ($searchTerm) {
        $q->where('nome', 'ILIKE', "%{$searchTerm}%")->orWhere(
          'descricao',
          'ILIKE',
          "%{$searchTerm}%",
        );
      });
    }

    // Filtro por data exata
    if ($request->filled('data_publicacao')) {
      try {
        $dataFiltro = Carbon::parse($request->data_publicacao);

        if ($dataFiltro->isFuture()) {
          Log::warning(
            'Tentativa de busca com data futura: ' . $request->data_publicacao,
          );
          return $query->whereRaw('1 = 0');
        }

        $query->whereDate('data_publicacao', $dataFiltro->toDateString());
      } catch (\Exception $e) {
        Log::warning('Data inválida no filtro: ' . $request->data_publicacao);
        return $query->whereRaw('1 = 0');
      }
    }

    // Filtro por mês/ano
    if ($request->filled('mes_ano') && !$request->filled('data_publicacao')) {
      try {
        if (preg_match('/^(\d{4})-(\d{2})$/', $request->mes_ano, $matches)) {
          $mesAnoFormatado = $matches[1] . '-' . $matches[2];

          $mesAtual = Carbon::now()->format('Y-m');
          if ($mesAnoFormatado > $mesAtual) {
            Log::warning(
              'Tentativa de busca com mês futuro: ' . $request->mes_ano,
            );
            return $query->whereRaw('1 = 0');
          }

          $query->whereRaw("TO_CHAR(data_publicacao, 'YYYY-MM') = ?", [
            $mesAnoFormatado,
          ]);
        }
      } catch (\Exception $e) {
        Log::warning('Mês/ano inválido no filtro: ' . $request->mes_ano);
        return $query->whereRaw('1 = 0');
      }
    }

    return $query
      ->orderBy('data_publicacao', 'desc')
      ->orderBy('created_at', 'desc');
  }

  /**
   * Buscar boletins por mês específico
   */
  private function buscarBoletinsPorMes($query, $mesAno)
  {
    try {
      if (preg_match('/^(\d{4})-(\d{2})$/', $mesAno, $matches)) {
        $mesAnoFormatado = $matches[1] . '-' . $matches[2];

        $mesAtual = Carbon::now()->format('Y-m');
        if ($mesAnoFormatado > $mesAtual) {
          return $query->whereRaw('1 = 0');
        }

        return $query
          ->whereRaw("TO_CHAR(data_publicacao, 'YYYY-MM') = ?", [
            $mesAnoFormatado,
          ])
          ->orderBy('data_publicacao', 'desc')
          ->orderBy('created_at', 'desc');
      }
    } catch (\Exception $e) {
      Log::error('Erro ao buscar boletins do mês: ' . $e->getMessage());
    }

    // Fallback
    $mesAtual = Carbon::now()->format('Y-m');
    return $query
      ->whereRaw("TO_CHAR(data_publicacao, 'YYYY-MM') = ?", [$mesAtual])
      ->orderBy('data_publicacao', 'desc')
      ->orderBy('created_at', 'desc');
  }

  /**
   * Visualizar PDF público de boletim
   */
  public function viewBoletim($id)
  {
    if (!Auth::check()) {
      return redirect()
        ->route('login')
        ->with('message', 'Faça login para visualizar este boletim.')
        ->with('intended', request()->fullUrl());
    }

    try {
      $boletim = Boletim::where('status', true)->findOrFail($id);

      if (!$boletim->arquivo) {
        abort(404, 'Arquivo não encontrado');
      }

      if (!StorageHelper::boletins()->exists($boletim->arquivo)) {
        abort(404, 'Arquivo não encontrado no servidor: ' . $boletim->arquivo);
      }

      $conteudo = StorageHelper::boletins()->get($boletim->arquivo);

      return response($conteudo, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' =>
          'inline; filename="' . $boletim->nome . '.pdf"',
        'Cache-Control' => 'private, max-age=3600',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao visualizar boletim: ' . $e->getMessage());
      abort(404, 'Arquivo não encontrado');
    }
  }

  /**
   * Download de boletim
   */
  public function downloadBoletim($id)
  {
    if (!Auth::check()) {
      return redirect()
        ->route('login')
        ->with('message', 'Faça login para baixar este boletim.')
        ->with('intended', request()->fullUrl());
    }

    try {
      $boletim = Boletim::where('status', true)->findOrFail($id);

      if (!$boletim->arquivo) {
        abort(404, 'Arquivo não encontrado');
      }

      if (!StorageHelper::boletins()->exists($boletim->arquivo)) {
        abort(404, 'Arquivo não encontrado no servidor: ' . $boletim->arquivo);
      }

      $conteudo = StorageHelper::boletins()->get($boletim->arquivo);

      // Sanitizar nome do arquivo
      $nomeDownload = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $boletim->nome);
      $nomeDownload = preg_replace('/_+/', '_', $nomeDownload);
      $nomeDownload = trim($nomeDownload, '_') . '.pdf';

      return response($conteudo, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $nomeDownload . '"',
        'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao fazer download do boletim: ' . $e->getMessage());
      abort(404, 'Arquivo não encontrado');
    }
  }

  /**
   * Obter estatísticas do sistema
   */
  private function getSystemStats()
  {
    try {
      return [
        'total_normas' => Norma::where('status', true)->count(),
        'normas_vigentes' => Norma::where('status', true)
          ->where('vigente', 'VIGENTE')
          ->count(),
        'tipos_count' => Tipo::where('status', true)->count(),
        'orgaos_count' => Orgao::where('status', true)->count(),
        'usuarios_ativos' => User::where('active', true)->count(),
        'normas_cadastradas' => Norma::where('status', true)->count(),
        'em_analise' => Norma::where('status', true)
          ->where('vigente', 'EM ANÁLISE')
          ->count(),
        'nao_vigentes' => Norma::where('status', true)
          ->where('vigente', 'NÃO VIGENTE')
          ->count(),
      ];
    } catch (\Exception $e) {
      Log::error('Erro ao obter estatísticas: ' . $e->getMessage());
      return [
        'total_normas' => 0,
        'normas_vigentes' => 0,
        'tipos_count' => 0,
        'orgaos_count' => 0,
        'usuarios_ativos' => 0,
        'normas_cadastradas' => 0,
        'em_analise' => 0,
        'nao_vigentes' => 0,
      ];
    }
  }

  /**
   * Página inicial da SPA
   */
  public function home()
  {
    $stats = $this->getSystemStats();

    return Inertia::render('Home', [
      'stats' => $stats,
      'page' => 'home',
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
      'search_term',
      'tipo_id',
      'orgao_id',
      'vigente',
      'data_inicio',
      'data_fim',
      'page',
    ]);

    $normas = null;

    // Verificar se é uma busca ativa (quando clica no botão buscar)
    $isBusca =
      $request->has('busca') ||
      $request->anyFilled([
        'search_term',
        'tipo_id',
        'orgao_id',
        'vigente',
        'data_inicio',
        'data_fim',
      ]);

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
      'page' => 'consulta',
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
      ->where(function ($query) use ($norma) {
        $query
          ->where('tipo_id', $norma->tipo_id)
          ->orWhere('orgao_id', $norma->orgao_id);
      })
      ->with(['tipo', 'orgao'])
      ->limit(5)
      ->get();

    return Inertia::render('NormaView', [
      'norma' => $norma,
      'relacionadas' => $relacionadas,
      'stats' => $this->getSystemStats(),
      'page' => 'norma',
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
      'data' => $normas,
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

  // Realizar busca de normas com sistema de relevância
  private function performSearch(Request $request)
  {
    // Base query com eager loading
    $query = Norma::with([
      'tipo:id,tipo',
      'orgao:id,orgao',
      'palavrasChave:id,palavra_chave',
    ])->where('status', true);

    // Verificar se há algum filtro ativo
    $hasFilters = $request->anyFilled([
      'search_term',
      'tipo_id',
      'orgao_id',
      'vigente',
      'data_inicio',
      'data_fim',
    ]);

    // Se não há filtros, mostrar todas as normas ativas ordenadas por data
    if (!$hasFilters) {
      $query->orderBy('data', 'desc')->orderBy('id', 'desc');
    } else {
      // Aplicar filtros de pesquisa com relevância apenas se há termo de busca
      if ($request->filled('search_term')) {
        $query = $this->applySearchWithRelevance($query, $request->search_term);
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
        $dataInicio = Carbon::createFromFormat(
          'Y-m-d',
          $request->data_inicio,
        )->startOfDay();
        $query->where('data', '>=', $dataInicio);
      } catch (\Exception $e) {
        // Se erro na conversão de data, ignorar filtro
      }
    }

    if ($request->filled('data_fim')) {
      try {
        $dataFim = Carbon::createFromFormat(
          'Y-m-d',
          $request->data_fim,
        )->endOfDay();
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

  // Aplicar busca com sistema de relevância
  private function applySearchWithRelevance($query, $searchTerm)
  {
    $searchTerm = trim($searchTerm);

    if (empty($searchTerm)) {
      return $query;
    }

    // Dividir em palavras e filtrar palavras muito pequenas
    $words = array_filter(
      array_map('trim', explode(' ', $searchTerm)),
      function ($word) {
        return strlen($word) >= 2;
      },
    );

    if (empty($words)) {
      return $query;
    }

    // Criar subquery para calcular relevância
    $relevanceSelect = $this->buildRelevanceScorePostgreSQL(
      $words,
      $searchTerm,
    );

    return $query
      ->select('normas.*')
      ->selectRaw("({$relevanceSelect}) as relevance_score")
      ->where(function ($q) use ($words, $searchTerm) {
        // Busca nos campos principais
        foreach ($words as $word) {
          $q->where(function ($wordQuery) use ($word) {
            $wordQuery
              ->where('descricao', 'ILIKE', "%{$word}%")
              ->orWhere('resumo', 'ILIKE', "%{$word}%");
          });
        }

        // Busca nas palavras-chave
        $q->orWhereHas('palavrasChave', function ($subq) use ($words) {
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
  private function buildRelevanceScorePostgreSQL($words, $fullTerm)
  {
    $scoreQueries = [];

    // Score para frase exata (busca primeiro)
    $scoreQueries[] = "CASE WHEN descricao ILIKE '%{$fullTerm}%' THEN 10 ELSE 0 END";
    $scoreQueries[] = "CASE WHEN resumo ILIKE '%{$fullTerm}%' THEN 8 ELSE 0 END";

    // Score para palavras individuais
    foreach ($words as $index => $word) {
      $weight = max(1, 5 - $index); // Primeiras palavras têm peso maior
      $scoreQueries[] = "CASE WHEN descricao ILIKE '%{$word}%' THEN {$weight} ELSE 0 END";
      $scoreQueries[] =
        "CASE WHEN resumo ILIKE '%{$word}%' THEN " .
        ($weight - 1) .
        ' ELSE 0 END';
    }

    return implode(' + ', $scoreQueries);
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
            'usuario' => $especificacao->usuario
              ? [
                'id' => $especificacao->usuario->id,
                'name' => $especificacao->usuario->name,
              ]
              : null,
          ];
        });

      return Inertia::render('Especificacoes', [
        'especificacoes' => $especificacoes,
        'stats' => $this->getSystemStats(),
      ]);
    } catch (\Exception $e) {
      return Inertia::render('Especificacoes', [
        'especificacoes' => [],
        'error' => 'Erro ao carregar especificações técnicas',
      ]);
    }
  }

  /**
   * Download público de especificação
   */
  public function downloadEspecificacao($id)
  {
    try {
      $especificacao = Especificacao::where('status', true)->findOrFail($id);

      if (!$especificacao->arquivo) {
        abort(404, 'Arquivo não encontrado');
      }

      // BUSCAR NO BUCKET 'especificacoes'
      if (!StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
        abort(
          404,
          'Arquivo não encontrado no servidor: ' . $especificacao->arquivo,
        );
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
      Log::error(
        'Erro ao fazer download da especificação: ' . $e->getMessage(),
      );
      abort(404, 'Arquivo não encontrado');
    }
  }

  /**
   * Visualizar PDF público de especificação
   */
  public function viewEspecificacao($id)
  {
    try {
      $especificacao = Especificacao::where('status', true)->findOrFail($id);

      if (!$especificacao->arquivo) {
        abort(404, 'Arquivo não encontrado');
      }

      // BUSCAR NO BUCKET 'especificacoes'
      if (!StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
        abort(
          404,
          'Arquivo não encontrado no servidor: ' . $especificacao->arquivo,
        );
      }

      // Buscar arquivo do MinIO
      $conteudo = StorageHelper::especificacoes()->get($especificacao->arquivo);

      return response($conteudo, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' =>
          'inline; filename="' . $especificacao->nome . '.pdf"',
        'Cache-Control' => 'public, max-age=3600',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao visualizar especificação: ' . $e->getMessage());
      abort(404, 'Arquivo não encontrado');
    }
  }

  /**
   * Buscar boletins que mencionam o usuário logado
   */
  private function buscarPorMeuNome(Request $request, $query)
  {
    if (!Auth::check()) {
      return $query->whereRaw('1 = 0');
    }

    $user = Auth::user();
    $matricula = $user->matricula;

    // Gerar variações da matrícula
    $variacoesMatricula = $this->gerarVariacoesMatricula($matricula);

    // Buscar apenas boletins indexados que contenham a matrícula
    $query
      ->where('indexado', true)
      ->where(function ($q) use ($variacoesMatricula, $matricula) {
        // Busca com ILIKE para cada variação da matrícula
        foreach ($variacoesMatricula as $variacao) {
          $q->orWhere('conteudo_indexado', 'ILIKE', "%{$variacao}%");
        }

        // Busca com expressão regular para pegar matrícula em diferentes formatos
        // Exemplo: 1234567, 01234567, Mat. 1234567, Matrícula: 1234567
        $matriculaSemZeros = ltrim($matricula, '0');
        if (!empty($matriculaSemZeros)) {
          $q->orWhereRaw('conteudo_indexado ~* ?', [
            "\\b0*{$matriculaSemZeros}\\b",
          ]);
        }
      });

    return $query
      ->orderBy('data_publicacao', 'desc')
      ->orderBy('created_at', 'desc');
  }

  private function gerarVariacoesMatricula(string $matricula): array
  {
    $variacoes = [];

    // Remove zeros à esquerda e espaços
    $matriculaSemZeros = ltrim(trim($matricula), '0');

    // Se ficou vazio (era só zeros), retornar apenas a matrícula original
    if (empty($matriculaSemZeros)) {
      return [$matricula];
    }

    // Validação: matrícula deve ter no máximo 8 dígitos
    if (strlen($matriculaSemZeros) > 8) {
      Log::warning("Matrícula com mais de 8 dígitos: {$matricula}");
      return [$matricula];
    }

    // Matrícula original (como está no banco)
    $variacoes[] = $matricula;

    // Matrícula sem zeros à esquerda
    if ($matriculaSemZeros !== $matricula) {
      $variacoes[] = $matriculaSemZeros;
    }

    // Matrícula com 7 dígitos
    $variacoes[] = str_pad($matriculaSemZeros, 7, '0', STR_PAD_LEFT);

    // Matrícula com 8 dígitos (limite máximo do sistema)
    if (strlen($matriculaSemZeros) <= 8) {
      $variacoes[] = str_pad($matriculaSemZeros, 8, '0', STR_PAD_LEFT);
    }

    // Matrícula com 6 dígitos (casos antigos/excepcionais)
    if (strlen($matriculaSemZeros) <= 6) {
      $variacoes[] = str_pad($matriculaSemZeros, 6, '0', STR_PAD_LEFT);
    }

    // Variações com formatação comum
    $formatacoes = [
      "mat. {$matriculaSemZeros}",
      "mat {$matriculaSemZeros}",
      "Mat. {$matriculaSemZeros}",
      "MAT. {$matriculaSemZeros}",
      "matrícula {$matriculaSemZeros}",
      "Matrícula {$matriculaSemZeros}",
      "MATRÍCULA {$matriculaSemZeros}",
      "matricula {$matriculaSemZeros}", // sem acento
      "Matricula {$matriculaSemZeros}", // sem acento
      "MATRICULA {$matriculaSemZeros}", // sem acento
      "nº {$matriculaSemZeros}", // formato alternativo
      "Nº {$matriculaSemZeros}",
      "n° {$matriculaSemZeros}",
    ];

    foreach ($formatacoes as $formato) {
      $variacoes[] = $formato;
    }

    // Variações com matrícula formatada (7 dígitos) nos textos
    $matricula7Digitos = str_pad($matriculaSemZeros, 7, '0', STR_PAD_LEFT);
    if ($matricula7Digitos !== $matriculaSemZeros) {
      $variacoes[] = "mat. {$matricula7Digitos}";
      $variacoes[] = "matrícula {$matricula7Digitos}";
      $variacoes[] = "matricula {$matricula7Digitos}";
    }

    // Remover duplicatas e valores vazios
    $variacoes = array_unique(
      array_filter($variacoes, function ($v) {
        return !empty(trim($v));
      }),
    );

    return array_values($variacoes);
  }
}

/**
 * Função auxiliar para sanitizar nome de arquivo
 */
if (!function_exists('sanitize_filename')) {
  function sanitize_filename($filename)
  {
    // Remove caracteres especiais e substitui espaços por underscores
    $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    return trim($filename, '_');
  }
}
