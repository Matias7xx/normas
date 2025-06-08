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
        'status'
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     * @var array
     */
    protected $casts = [
        'data' => 'date',
        'status' => 'boolean',
    ];

    // Relacionamentos

    public function publicidade()
    {
        return $this->belongsTo(Publicidade::class, 'publicidade_id', 'id')->select('id', 'publicidade');
    }

    public function orgao()
    {
        return $this->belongsTo(Orgao::class, 'orgao_id', 'id')->select('id', 'orgao');
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
            'palavra_chave_id'
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
        
        return Storage::url('public/normas/' . $this->anexo);
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
     * Scopes Aprimorados
     */
    
    public function scopeAtivas($query)
    {
        return $query->where('status', true);
    }
    
    public function scopePorPalavraChave($query, $palavraChave)
    {
        return $query->whereHas('palavrasChave', function($q) use ($palavraChave) {
            $q->where('palavra_chave', 'ILIKE', "%{$palavraChave}%")
              ->where('normas_chaves.status', true);
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
        
        return $query->where(function($q) use ($termo) {
            $q->where('descricao', 'ILIKE', "%{$termo}%")
              ->orWhere('resumo', 'ILIKE', "%{$termo}%")
              ->orWhereHas('orgao', function($subq) use ($termo) {
                  $subq->where('orgao', 'ILIKE', "%{$termo}%");
              })
              ->orWhereHas('tipo', function($subq) use ($termo) {
                  $subq->where('tipo', 'ILIKE', "%{$termo}%");
              })
              ->orWhereHas('palavrasChave', function($subq) use ($termo) {
                  $subq->where('palavra_chave', 'ILIKE', "%{$termo}%")
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
        return $query->whereMonth('data', now()->month)
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
        
        if (!empty($filtros['palavra_chave'])) {
            $query->porPalavraChave($filtros['palavra_chave']);
        }
        
        if (!empty($filtros['data_inicio']) || !empty($filtros['data_fim'])) {
            $query->porPeriodo(
                $filtros['data_inicio'] ?? null,
                $filtros['data_fim'] ?? null
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
        return $query->join('tipos', 'normas.tipo_id', '=', 'tipos.id')
                    ->orderBy('tipos.tipo', $direcao)
                    ->select('normas.*');
    }
    
    public function scopeOrdenadoPorOrgao($query, $direcao = 'asc')
    {
        return $query->join('orgaos', 'normas.orgao_id', '=', 'orgaos.id')
                    ->orderBy('orgaos.orgao', $direcao)
                    ->select('normas.*');
    }
    
    /**
     * Métodos utilitários estáticos
     */
    
    /**
     * Obtém estatísticas rápidas
     */
    public static function obterEstatisticas()
    {
        return [
            'total' => self::ativas()->count(),
            'este_mes' => self::ativas()->esteMes()->count(),
            'este_ano' => self::ativas()->esteAno()->count(),
            'recentes' => self::ativas()->recentes(7)->count()
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
            ->whereHas('palavrasChave', function($query) use ($palavrasChaveIds) {
                $query->whereIn('palavra_chaves.id', $palavrasChaveIds);
            })
            ->withCount(['palavrasChave' => function($query) use ($palavrasChaveIds) {
                $query->whereIn('palavra_chaves.id', $palavrasChaveIds);
            }])
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
}