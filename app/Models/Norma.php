<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Norma extends Model
{
  use HasFactory;

  /**
   * @var array
   */
  protected $fillable = [
    'usuario_id',
    'data',
    'descricao',
    'resumo',
    'publicidade_id',
    'tipo_id',
    'orgao_id',
    'anexo',
    'vigente',
    'status',
    'vigencia_indeterminada',
    'data_limite_vigencia',
  ];

  /**
   * Atributos que devem ser convertidos para tipos nativos.
   * @var array
   */
  protected $casts = [
    'data' => 'date',
    'status' => 'boolean',
    'vigencia_indeterminada' => 'boolean',
    'data_limite_vigencia' => 'date',
  ];

  const VIGENTE_VIGENTE = 'VIGENTE';
  const VIGENTE_NAO_VIGENTE = 'NÃO VIGENTE';
  const VIGENTE_EM_ANALISE = 'EM ANÁLISE';

  public static function getVigenteOptions()
  {
    return [
      self::VIGENTE_VIGENTE => 'VIGENTE',
      self::VIGENTE_NAO_VIGENTE => 'NÃO VIGENTE',
      self::VIGENTE_EM_ANALISE => 'EM ANÁLISE',
    ];
  }

  // Relacionamentos

  public function publicidade()
  {
    return $this->belongsTo(Publicidade::class, 'publicidade_id', 'id')->select(
      'id',
      'publicidade',
    );
  }

  public function orgao()
  {
    return $this->belongsTo(Orgao::class, 'orgao_id', 'id')->select(
      'id',
      'orgao',
    );
  }

  public function tipo()
  {
    return $this->belongsTo(Tipo::class, 'tipo_id', 'id')->select('id', 'tipo');
  }

  public function palavrasChave()
  {
    return $this->belongsToMany(
      PalavraChave::class,
      'normas_chaves',
      'norma_id',
      'palavra_chave_id',
    )->wherePivot('status', true);
  }

  public function usuario()
  {
    return $this->belongsTo(\App\Models\User::class, 'usuario_id', 'id');
  }

  /**
   * Accessors
   */

  public function getAnexoUrlAttribute()
  {
    if (!$this->anexo) {
      return null;
    }

    // Verifica se o arquivo existe antes de retornar a URL
    if (Storage::disk('public')->exists('normas/' . $this->anexo)) {
      return asset('storage/normas/' . $this->anexo);
    }

    return null;
  }

  public function hasAnexo()
  {
    return $this->anexo &&
      Storage::disk('public')->exists('normas/' . $this->anexo);
  }

  public function getDataFormatadaAttribute()
  {
    return $this->data ? $this->data->format('d/m/Y') : '';
  }

  public function getDescricaoResumidaAttribute($length = 50)
  {
    return \Illuminate\Support\Str::limit($this->descricao, $length, '...');
  }

  public function getResumoResumidoAttribute($length = 80)
  {
    return \Illuminate\Support\Str::limit($this->resumo, $length, '...');
  }

  /**
   * Accessor para retornar a classe CSS baseada no status de vigência
   */
  public function getVigenteClassAttribute()
  {
    switch ($this->vigente) {
      case self::VIGENTE_VIGENTE:
        return 'badge-success';
      case self::VIGENTE_NAO_VIGENTE:
        return 'badge-danger';
      case self::VIGENTE_EM_ANALISE:
        return 'badge-warning';
      default:
        return 'badge-secondary';
    }
  }

  public function getVigenteIconAttribute()
  {
    switch ($this->vigente) {
      case self::VIGENTE_VIGENTE:
        return 'fas fa-check-circle';
      case self::VIGENTE_NAO_VIGENTE:
        return 'fas fa-times-circle';
      case self::VIGENTE_EM_ANALISE:
        return 'fas fa-clock';
      default:
        return 'fas fa-question-circle';
    }
  }

  /**
   * Scopes Aprimorados
   */

  public function scopeAtivas($query)
  {
    return $query->where('status', true);
  }

  public function scopeVigentes($query)
  {
    return $query->where('vigente', self::VIGENTE_VIGENTE);
  }

  public function scopeNaoVigentes($query)
  {
    return $query->where('vigente', self::VIGENTE_NAO_VIGENTE);
  }

  public function scopeEmAnalise($query)
  {
    return $query->where('vigente', self::VIGENTE_EM_ANALISE);
  }

  public function scopePorVigencia($query, $vigencia)
  {
    if (empty($vigencia)) {
      return $query;
    }

    return $query->where('vigente', $vigencia);
  }

  public function scopePorPalavraChave($query, $palavraChave)
  {
    return $query->whereHas('palavrasChave', function ($q) use ($palavraChave) {
      $q->where('palavra_chave', 'ILIKE', "%{$palavraChave}%")->where(
        'normas_chaves.status',
        true,
      );
    });
  }

  /**
   * Busca geral aprimorada - pesquisa em múltiplos campos
   */
  public function scopePesquisaGeral($query, $termo)
  {
    if (empty($termo)) {
      return $query;
    }

    return $query->where(function ($q) use ($termo) {
      $q->where('descricao', 'ILIKE', "%{$termo}%")
        ->orWhere('resumo', 'ILIKE', "%{$termo}%")
        ->orWhereHas('orgao', function ($subq) use ($termo) {
          $subq->where('orgao', 'ILIKE', "%{$termo}%");
        })
        ->orWhereHas('tipo', function ($subq) use ($termo) {
          $subq->where('tipo', 'ILIKE', "%{$termo}%");
        })
        ->orWhereHas('palavrasChave', function ($subq) use ($termo) {
          $subq
            ->where('palavra_chave', 'ILIKE', "%{$termo}%")
            ->where('normas_chaves.status', true);
        });
    });
  }

  public function scopePorTipo($query, $tipoId)
  {
    if (empty($tipoId)) {
      return $query;
    }

    return $query->where('tipo_id', $tipoId);
  }

  public function scopePorOrgao($query, $orgaoId)
  {
    if (empty($orgaoId)) {
      return $query;
    }

    return $query->where('orgao_id', $orgaoId);
  }

  /**
   * Filtro por período aprimorado
   */
  public function scopePorPeriodo($query, $dataInicio, $dataFim)
  {
    if (empty($dataInicio) && empty($dataFim)) {
      return $query;
    }

    if (!empty($dataInicio) && empty($dataFim)) {
      return $query->where('data', '>=', $dataInicio);
    }

    if (empty($dataInicio) && !empty($dataFim)) {
      return $query->where('data', '<=', $dataFim);
    }

    return $query->whereBetween('data', [$dataInicio, $dataFim]);
  }

  /**
   * Scope para filtros rápidos de tempo
   */
  public function scopeRecentes($query, $dias = 30)
  {
    return $query->where('data', '>=', now()->subDays($dias));
  }

  public function scopeEsteMes($query)
  {
    return $query
      ->whereMonth('data', now()->month)
      ->whereYear('data', now()->year);
  }

  public function scopeEsteAno($query)
  {
    return $query->whereYear('data', now()->year);
  }

  public function scopeUltimosTresMeses($query)
  {
    $tresMesesAtras = now()->subMonths(3);
    return $query->where('data', '>=', $tresMesesAtras);
  }

  /**
   * Scope para busca avançada com múltiplos critérios
   */
  public function scopeBuscaAvancada($query, array $filtros)
  {
    $query->ativas();

    if (!empty($filtros['termo'])) {
      $query->pesquisaGeral($filtros['termo']);
    }

    if (!empty($filtros['tipo_id'])) {
      $query->porTipo($filtros['tipo_id']);
    }

    if (!empty($filtros['orgao_id'])) {
      $query->porOrgao($filtros['orgao_id']);
    }

    if (!empty($filtros['vigente'])) {
      $query->porVigencia($filtros['vigente']);
    }

    if (!empty($filtros['palavra_chave'])) {
      $query->porPalavraChave($filtros['palavra_chave']);
    }

    if (!empty($filtros['data_inicio']) || !empty($filtros['data_fim'])) {
      $query->porPeriodo(
        $filtros['data_inicio'] ?? null,
        $filtros['data_fim'] ?? null,
      );
    }

    return $query;
  }

  public function scopePorPublicidade($query, $publicidadeId)
  {
    if (empty($publicidadeId)) {
      return $query;
    }

    return $query->where('publicidade_id', $publicidadeId);
  }

  public function scopePorUsuario($query, $usuarioId)
  {
    if (empty($usuarioId)) {
      return $query;
    }

    return $query->where('usuario_id', $usuarioId);
  }

  /**
   * Scopes para ordenação otimizada
   */
  public function scopeOrdenadoPorData($query, $direcao = 'desc')
  {
    return $query->orderBy('data', $direcao)->orderBy('id', $direcao);
  }

  public function scopeOrdenadoPorId($query, $direcao = 'desc')
  {
    return $query->orderBy('id', $direcao);
  }

  public function scopeOrdenadoPorTipo($query, $direcao = 'asc')
  {
    return $query
      ->join('tipos', 'normas.tipo_id', '=', 'tipos.id')
      ->orderBy('tipos.tipo', $direcao)
      ->select('normas.*');
  }

  public function scopeOrdenadoPorOrgao($query, $direcao = 'asc')
  {
    return $query
      ->join('orgaos', 'normas.orgao_id', '=', 'orgaos.id')
      ->orderBy('orgaos.orgao', $direcao)
      ->select('normas.*');
  }

  public function scopeOrdenadoPorVigencia($query, $direcao = 'asc')
  {
    // Ordem personalizada: VIGENTE, EM ANÁLISE, NÃO VIGENTE
    $order = $direcao === 'desc' ? 'desc' : 'asc';

    return $query->orderByRaw(
      "
            CASE vigente 
                WHEN 'VIGENTE' THEN 1 
                WHEN 'EM ANÁLISE' THEN 2 
                WHEN 'NÃO VIGENTE' THEN 3 
                ELSE 4 
            END " . $order,
    );
  }

  /**
   * Métodos utilitários estáticos
   */

  /**
   * Obtém estatísticas rápidas incluindo status de vigência
   */
  public static function obterEstatisticas()
  {
    return [
      'total' => self::ativas()->count(),
      'vigentes' => self::ativas()->vigentes()->count(),
      'nao_vigentes' => self::ativas()->naoVigentes()->count(),
      'em_analise' => self::ativas()->emAnalise()->count(),
      'este_mes' => self::ativas()->esteMes()->count(),
      'este_ano' => self::ativas()->esteAno()->count(),
      'recentes' => self::ativas()->recentes(7)->count(),
    ];
  }

  /**
   * Busca normas relacionadas baseada em palavras-chave
   */
  public function normasRelacionadas($limit = 5)
  {
    if ($this->palavrasChave->isEmpty()) {
      return collect();
    }

    $palavrasChaveIds = $this->palavrasChave->pluck('id');

    return self::ativas()
      ->where('id', '!=', $this->id)
      ->whereHas('palavrasChave', function ($query) use ($palavrasChaveIds) {
        $query->whereIn('palavra_chaves.id', $palavrasChaveIds);
      })
      ->withCount([
        'palavrasChave' => function ($query) use ($palavrasChaveIds) {
          $query->whereIn('palavra_chaves.id', $palavrasChaveIds);
        },
      ])
      ->orderBy('palavras_chave_count', 'desc')
      ->orderBy('data', 'desc')
      ->limit($limit)
      ->get();
  }

  /**
   * Verifica se a norma é recente (últimos 30 dias)
   */
  public function isRecente($dias = 30)
  {
    if (!$this->data) {
      return false;
    }

    return $this->data->diffInDays(now()) <= $dias;
  }

  /**
   * Obtém a idade da norma em anos
   */
  public function getIdadeAnos()
  {
    if (!$this->data) {
      return null;
    }

    return $this->data->diffInYears(now());
  }

  /**
   * Verifica se a norma está vigente
   */
  public function isVigente()
  {
    return $this->vigente === self::VIGENTE_VIGENTE;
  }

  /**
   * Verifica se a norma não está vigente
   */
  public function isNaoVigente()
  {
    return $this->vigente === self::VIGENTE_NAO_VIGENTE;
  }

  /**
   * Verifica se a norma está em análise
   */
  public function isEmAnalise()
  {
    return $this->vigente === self::VIGENTE_EM_ANALISE;
  }

  public function getAuditoriaInfo()
  {
    return [
      'usuario_nome' => $this->usuario
        ? $this->usuario->name
        : 'Usuário não encontrado',
      'usuario_matricula' => $this->usuario ? $this->usuario->matricula : null,
      'data_cadastro' => $this->created_at
        ? $this->created_at->format('d/m/Y H:i')
        : null,
      'data_atualizacao' => $this->updated_at
        ? $this->updated_at->format('d/m/Y H:i')
        : null,
    ];
  }

  /**
   * Scope para normas com vigência programada para expirar
   */
  public function scopeComVigenciaProgramada($query, $diasAntes = 0)
  {
    $dataLimite = now()->addDays($diasAntes)->toDateString();

    return $query
      ->where('vigencia_indeterminada', false)
      ->whereNotNull('data_limite_vigencia')
      ->whereDate('data_limite_vigencia', '<=', $dataLimite);
  }

  /**
   * Scope para normas que devem ter o status alterado hoje
   */
  public function scopeParaAtualizarHoje($query)
  {
    return $query->comVigenciaProgramada(0);
  }

  /**
   * Scope para normas que vão expirar nos próximos X dias
   */
  public function scopeVencendoEm($query, $dias = 7)
  {
    $dataInicio = now()->toDateString();
    $dataFim = now()->addDays($dias)->toDateString();

    return $query
      ->where('vigencia_indeterminada', false)
      ->whereNotNull('data_limite_vigencia')
      ->whereBetween('data_limite_vigencia', [$dataInicio, $dataFim]);
  }

  /**
   * Verifica se a norma tem vigência programada
   */
  public function temVigenciaProgramada()
  {
    return !$this->vigencia_indeterminada && $this->data_limite_vigencia;
  }

  /**
   * Verifica se a norma deve mudar de status hoje
   */
  public function deveAtualizarHoje()
  {
    if (!$this->temVigenciaProgramada()) {
      return false;
    }

    return $this->data_limite_vigencia->isToday() ||
      $this->data_limite_vigencia->isPast();
  }

  /**
   * Retorna quantos dias faltam para a mudança de vigência
   */
  public function diasParaMudancaVigencia()
  {
    if (!$this->temVigenciaProgramada()) {
      return null;
    }

    return now()->diffInDays($this->data_limite_vigencia, false);
  }

  /**
   * Retorna o próximo status que a norma terá
   */
  public function getProximoStatusAttribute()
  {
    if (!$this->temVigenciaProgramada()) {
      return null;
    }

    switch ($this->vigente) {
      case self::VIGENTE_VIGENTE:
        return self::VIGENTE_NAO_VIGENTE;
      case self::VIGENTE_NAO_VIGENTE:
        return self::VIGENTE_VIGENTE;
      case self::VIGENTE_EM_ANALISE:
        return self::VIGENTE_VIGENTE;
      default:
        return null;
    }
  }

  /**
   * Accessor para mostrar informações de vigência programada
   */
  public function getInfoVigenciaProgramadaAttribute()
  {
    if (!$this->temVigenciaProgramada()) {
      return null;
    }

    $dias = $this->diasParaMudancaVigencia();
    $proximoStatus = $this->proximo_status;
    $dataFormatada = $this->data_limite_vigencia->format('d/m/Y');

    if ($dias < 0) {
      return "Deveria ter mudado para '{$proximoStatus}' em {$dataFormatada} (" .
        abs($dias) .
        ' dias atrás)';
    } elseif ($dias == 0) {
      return "Muda para '{$proximoStatus}' hoje ({$dataFormatada})";
    } else {
      return "Mudará para '{$proximoStatus}' em {$dias} dias ({$dataFormatada})";
    }
  }

  /**
   * Método estático para obter estatísticas de vigência programada
   */
  public static function obterEstatisticasVigenciaProgramada()
  {
    return [
      'total_programadas' => self::ativas()
        ->where('vigencia_indeterminada', false)
        ->whereNotNull('data_limite_vigencia')
        ->count(),
      'vencendo_hoje' => self::ativas()->paraAtualizarHoje()->count(),
      'vencendo_7_dias' => self::ativas()->vencendoEm(7)->count(),
      'vencendo_30_dias' => self::ativas()->vencendoEm(30)->count(),
      'atrasadas' => self::ativas()
        ->comVigenciaProgramada(-365)
        ->whereDate('data_limite_vigencia', '<', now())
        ->count(),
    ];
  }
}
