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
use App\Services\PdfTextExtractorService;

class BoletimController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    // Middleware para verificar se é root (1) ou role 7
    $this->middleware(function ($request, $next) {
      if (!in_array(Auth::user()->role_id, [1, 7])) {
        abort(
          403,
          'Acesso negado. Apenas usuários autorizados podem acessar esta área.',
        );
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

      $temFiltros = $request->anyFilled([
        'search_term',
        'data_publicacao',
        'mes_ano',
      ]);
      $isBusca = $request->has('busca') || $temFiltros;

      if ($isBusca) {
        $boletins = $this->executarBuscaBoletins($request, $query);
      } else {
        // Mostrar boletins do mês atual por padrão
        $mesAtual = Carbon::now()->format('Y-m');
        $boletins = $this->buscarBoletinsPorMes($query, $mesAtual);
      }

      $boletins = $boletins->paginate(40);
      $boletins->appends(
        $request->only(['search_term', 'data_publicacao', 'mes_ano', 'busca']),
      );

      return view('boletins.boletim_list', [
        'boletins' => $boletins,
        'filtros' => $request->only([
          'search_term',
          'data_publicacao',
          'mes_ano',
        ]),
        'mostrandoMesAtual' => !$isBusca,
        'mesAtual' => Carbon::now()->format('Y-m'),
        'totalEncontrados' => $boletins->total(),
        'expandirFiltros' =>
          $isBusca || $request->get('expandir_filtros', true),
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao listar boletins: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());

      return redirect()
        ->route('boletins.index')
        ->withErrors([
          'Erro ao carregar lista de boletins: ' . $e->getMessage(),
        ]);
    }
  }

  /**
   * Executar busca de boletins
   */
  private function executarBuscaBoletins(Request $request, $query)
  {
    // Filtro por termo de busca
    if ($request->filled('search_term')) {
      $term = trim($request->search_term);

      $query->where(function ($q) use ($term) {
        // Busca tradicional em nome/descricao
        $q->where('nome', 'ILIKE', "%{$term}%")->orWhere(
          'descricao',
          'ILIKE',
          "%{$term}%",
        );

        $q->orWhereRaw(
          "to_tsvector('portuguese', conteudo_indexado) @@ plainto_tsquery('portuguese', ?)",
          [$term],
        );
      });

      // Ordenar por relevância quando tem busca no conteúdo
      $query->orderByRaw(
        "ts_rank_cd(to_tsvector('portuguese', conteudo_indexado), plainto_tsquery('portuguese', ?)) DESC",
        [$term],
      );
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
      $caminhoCompleto = $this->generateUniqueFileName(
        $arquivo,
        $request->nome,
        $request->data_publicacao,
      );

      // Salvar no bucket 'boletins' com a estrutura de pastas ano/mes/
      $uploaded = StorageHelper::boletins()->putFileAs(
        '',
        $arquivo,
        $caminhoCompleto,
      );

      if (!$uploaded) {
        return back()
          ->withErrors(['Erro ao fazer upload do arquivo.'])
          ->withInput();
      }

      // Criar registro no banco - salvar o caminho completo
      $boletim = Boletim::create([
        'nome' => $request->nome,
        'descricao' => $request->descricao,
        'data_publicacao' => $request->data_publicacao,
        'arquivo' => $caminhoCompleto,
        'user_id' => Auth::id(),
        'status' => true,
      ]);

      $this->indexarBoletim($boletim);

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
      $caminhoArquivoAtual = $boletim->arquivo;
      $arquivoAlterado = false;

      // Se enviou novo arquivo
      if ($request->hasFile('arquivo')) {
        $arquivo = $request->file('arquivo');
        $novoCaminhoArquivo = $this->generateUniqueFileName(
          $arquivo,
          $request->nome,
          $request->data_publicacao,
        );

        // Upload do novo arquivo
        $uploaded = StorageHelper::boletins()->putFileAs(
          '',
          $arquivo,
          $novoCaminhoArquivo,
        );

        if ($uploaded) {
          // Remove arquivo antigo se conseguiu fazer upload do novo
          if (
            $caminhoArquivoAtual &&
            StorageHelper::boletins()->exists($caminhoArquivoAtual)
          ) {
            StorageHelper::boletins()->delete($caminhoArquivoAtual);
          }
          $caminhoArquivoAtual = $novoCaminhoArquivo;
          $arquivoAlterado = true;
        } else {
          return back()
            ->withErrors(['Erro ao fazer upload do novo arquivo.'])
            ->withInput();
        }
      }

      // Atualizar registro
      $boletim->update([
        'nome' => $request->nome,
        'descricao' => $request->descricao,
        'data_publicacao' => $request->data_publicacao,
        'arquivo' => $caminhoArquivoAtual,
      ]);

      if ($arquivoAlterado) {
        $this->indexarBoletim($boletim);
        return redirect()
          ->route('boletins.index')
          ->withSuccess('Boletim atualizado e re-indexado com sucesso!');
      }

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
        'Content-Disposition' =>
          'attachment; filename="' . $boletim->nome_arquivo_download . '"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao fazer download do boletim: ' . $e->getMessage());
      return back()->withErrors(['Erro ao fazer download do arquivo.']);
    }
  }

  /**
   * Indexa o conteúdo do boletim
   */
  private function indexarBoletim(Boletim $boletim)
  {
    try {
      $extractor = new PdfTextExtractorService();
      $texto = $extractor->extractText($boletim->arquivo);

      $boletim->update([
        'conteudo_indexado' => $texto,
        'indexado' => true,
        'indexado_em' => now(),
      ]);

      $tamanhoTexto = strlen($texto ?? '');
      Log::info(
        "Boletim {$boletim->id} indexado com sucesso - {$tamanhoTexto} caracteres",
      );
    } catch (\Exception $e) {
      // Se der erro, apenas loga mas não quebra o cadastro
      Log::error("Erro ao indexar boletim {$boletim->id}: " . $e->getMessage());

      $boletim->update([
        'indexado' => false,
        'conteudo_indexado' => null,
      ]);
    }
  }

  /**
   * Página de gerenciamento de indexação (apenas root)
   */
  public function indexacao()
  {
    $stats = [
      'total' => Boletim::ativos()->count(),
      'indexados' => Boletim::ativos()->where('indexado', true)->count(),
      'pendentes' => Boletim::ativos()->pendentesIndexacao()->count(),
    ];

    return view('boletins.indexacao', compact('stats'));
  }

  /**
   * Iniciar indexação
   */
  public function iniciarIndexacao(Request $request)
  {
    set_time_limit(900); // 15 minutos
    ini_set('memory_limit', '512M'); // Aumentar memória

    try {
      $tipo = $request->input('tipo', 'pendentes');

      Log::info('=== INICIANDO INDEXAÇÃO ===', [
        'tipo' => $tipo,
        'usuario' => Auth::user()->name ?? 'Sistema',
      ]);

      $query = Boletim::ativos();

      if ($tipo === 'todos') {
        // Primeiro: LIMPAR todos os campos de indexação
        Boletim::ativos()->update([
          'conteudo_indexado' => null,
          'indexado' => false,
          'indexado_em' => null,
        ]);

        Log::info(
          'Campos de indexação limpos. Iniciando re-indexação completa...',
        );

        $mensagemSucesso = 'Todos os boletins foram re-indexados do zero!';
      } else {
        // tipo = 'pendentes' apenas os que nunca foram indexados
        $query->pendentesIndexacao();
        $mensagemSucesso = 'Boletins pendentes indexados com sucesso!';
      }

      // Pegar boletins para processar
      $boletins = $query->get();

      if ($boletins->isEmpty()) {
        return back()->withInfo('Nenhum boletim para indexar!');
      }

      Log::info('Processando boletins...', [
        'quantidade' => $boletins->count(),
        'tipo' => $tipo,
      ]);

      $sucesso = 0;
      $erros = 0;

      foreach ($boletins as $boletim) {
        try {
          $this->indexarBoletim($boletim);
          $sucesso++;

          // Log a cada 10 boletins para acompanhar progresso
          if ($sucesso % 10 === 0) {
            Log::info(
              "Progresso da indexação: {$sucesso} de {$boletins->count()}",
            );
          }
        } catch (\Exception $e) {
          $erros++;
          Log::error(
            "Erro ao indexar boletim {$boletim->id}: " . $e->getMessage(),
          );
        }
      }

      Log::info('=== INDEXAÇÃO CONCLUÍDA ===', [
        'tipo' => $tipo,
        'sucesso' => $sucesso,
        'erros' => $erros,
        'total' => $boletins->count(),
      ]);

      $mensagemCompleta = "{$mensagemSucesso} (Sucesso: {$sucesso}";
      if ($erros > 0) {
        $mensagemCompleta .= ", Erros: {$erros})";
      } else {
        $mensagemCompleta .= ')';
      }

      return back()->withSuccess($mensagemCompleta);
    } catch (\Exception $e) {
      Log::error('Erro ao iniciar indexação: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return back()->withErrors([
        'Erro ao iniciar indexação: ' . $e->getMessage(),
      ]);
    }
  }

  /**
   * Busca boletins que mencionam o usuário
   */
  public function meuNome(Request $request)
  {
    try {
      $user = Auth::user();
      $nomeCompleto = $user->name;

      // Variações do nome para buscar
      $variacoesNome = $this->gerarVariacoesNome($nomeCompleto);

      $query = Boletim::with('usuario')
        ->ativos()
        ->where('indexado', true)
        ->where(function ($q) use ($variacoesNome) {
          foreach ($variacoesNome as $variacao) {
            $q->orWhere('conteudo_indexado', 'ILIKE', "%{$variacao}%");
          }
        });

      $boletins = $query->orderBy('data_publicacao', 'desc')->paginate(20);

      // Adicionar contexto de onde o nome aparece
      foreach ($boletins as $boletim) {
        $boletim->contexto = $this->extrairContexto(
          $boletim->conteudo_indexado,
          $variacoesNome,
        );
      }

      return view('boletins.boletim_meu_nome', [
        'boletins' => $boletins,
        'nomeUsuario' => $nomeCompleto,
        'totalEncontrados' => $boletins->total(),
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao buscar boletins com meu nome: ' . $e->getMessage());
      return redirect()
        ->route('boletins.index')
        ->withErrors(['Erro ao buscar boletins.']);
    }
  }

  /**
   * Gera variações do nome para buscar -> será usado apenas a matrícula
   */
  private function gerarVariacoesNome(string $nomeCompleto): array
  {
    $variacoes = [];

    // Nome completo
    $variacoes[] = $nomeCompleto;

    // Nome em maiúsculas
    $variacoes[] = strtoupper($nomeCompleto);

    // Apenas sobrenomes (últimos nomes)
    $partes = explode(' ', $nomeCompleto);
    if (count($partes) > 1) {
      $sobrenomes = implode(' ', array_slice($partes, -2));
      $variacoes[] = $sobrenomes;
      $variacoes[] = strtoupper($sobrenomes);
    }

    return array_unique($variacoes);
  }

  /**
   * Extrai contexto onde o nome aparece
   */
  private function extrairContexto(
    string $texto,
    array $variacoes,
    int $caracteres = 150,
  ): ?string {
    foreach ($variacoes as $variacao) {
      $pos = stripos($texto, $variacao);
      if ($pos !== false) {
        $inicio = max(0, $pos - $caracteres);
        $fim = min(strlen($texto), $pos + strlen($variacao) + $caracteres);

        $contexto = substr($texto, $inicio, $fim - $inicio);
        return '...' . trim($contexto) . '...';
      }
    }

    return null;
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
        'Content-Disposition' =>
          'inline; filename="' . $boletim->nome . '.pdf"',
        'Cache-Control' => 'public, max-age=3600',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao visualizar boletim: ' . $e->getMessage());
      abort(404, 'Arquivo não encontrado');
    }
  }

  /**
   * Gera nome único para o arquivo com estrutura de pastas baseado na data de publicação
   * ano/mes/nome_arquivo.pdf
   */
  private function generateUniqueFileName($file, $nomeBoletim, $dataPublicacao)
  {
    $extension = $file->getClientOriginalExtension();
    $nomeBoletimSanitizado = $this->sanitize_filename($nomeBoletim);

    // Criar estrutura de pasta baseada na data de publicação
    $dataCarbon = Carbon::parse($dataPublicacao);
    $ano = $dataCarbon->format('Y');
    $mes = $dataCarbon->format('m');

    // Construir nome do arquivo
    $nomeArquivo = $nomeBoletimSanitizado . '.' . $extension;

    // Verificar se já existe um arquivo com o mesmo nome na mesma pasta
    $caminhoCompleto = $ano . '/' . $mes . '/' . $nomeArquivo;
    $contador = 1;

    while (StorageHelper::boletins()->exists($caminhoCompleto)) {
      $nomeArquivoComContador =
        $nomeBoletimSanitizado . '_' . $contador . '.' . $extension;
      $caminhoCompleto = $ano . '/' . $mes . '/' . $nomeArquivoComContador;
      $contador++;
    }

    return $caminhoCompleto;
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
