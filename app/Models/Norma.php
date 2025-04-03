<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    // Retorna o tipo de publicidade da norma

    public function publicidade()
    {
        return $this->belongsTo(Publicidade::class, 'publicidade_id', 'id')->select('id', 'publicidade');
    }

     //Retorna o órgão relacionado à norma

    public function orgao()
    {
        return $this->belongsTo(Orgao::class, 'orgao_id', 'id')->select('id', 'orgao');
    }

     //Retorna o tipo da norma

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'tipo_id', 'id')->select('id', 'tipo');
    }

    
    //Retorna as palavras-chave relacionadas à norma

    public function palavrasChave()
    {
        return $this->belongsToMany(
            PalavraChave::class, 
            'normas_chaves', 
            'norma_id', 
            'palavra_chave_id'
        )->wherePivot('status', true);
    }
    
    
    /* Retorna o usuário que criou a norma */

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id', 'id');
    }

    /**
     * Acessors
     */
    
    /**
     * Retorna o URL completo para o anexo
     * @return string|null
     */
    public function getAnexoUrlAttribute()
    {
        if (!$this->anexo) {
            return null;
        }
        
        return Storage::url('public/normas/' . $this->anexo);
    }
    
    /**
     * Formata a data para exibição
     * @return string
     */
    public function getDataFormatadaAttribute()
    {
        return $this->data ? $this->data->format('d/m/Y') : '';
    }
    
    /**
     * Retorna um resumo limitado da descrição
     * @param int $length
     * @return string
     */
    public function getDescricaoResumidaAttribute($length = 50)
    {
        return \Illuminate\Support\Str::limit($this->descricao, $length, '...');
    }
    
    /**
     * Retorna um resumo limitado do texto do resumo
     * @param int $length
     * @return string
     */
    public function getResumoResumidoAttribute($length = 80)
    {
        return \Illuminate\Support\Str::limit($this->resumo, $length, '...');
    }
    
    /**
     * Scopes
     */
    
    /**
     * Filtra apenas normas ativas
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtivas($query)
    {
        return $query->where('status', true);
    }
    
    /**
     * Filtra por palavra-chave
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $palavraChave
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorPalavraChave($query, $palavraChave)
    {
        return $query->whereHas('palavrasChave', function($q) use ($palavraChave) {
            $q->where('palavra_chave', 'ILIKE', "%{$palavraChave}%")
              ->where('normas_chaves.status', true);
        });
    }
    
    /**
     * Filtra por termo de pesquisa em múltiplos campos
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $termo
     * @return \Illuminate\Database\Eloquent\Builder
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
                  $subq->where('palavra_chave', 'ILIKE', "%{$termo}%");
              });
        });
    }
    
    /**
     * Filtra normas pelo tipo
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $tipoId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorTipo($query, $tipoId)
    {
        if (empty($tipoId)) {
            return $query;
        }
        
        return $query->where('tipo_id', $tipoId);
    }
    
    /**
     * Filtra normas pelo órgão
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $orgaoId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorOrgao($query, $orgaoId)
    {
        if (empty($orgaoId)) {
            return $query;
        }
        
        return $query->where('orgao_id', $orgaoId);
    }
    
    /**
     * Filtra normas por período de data
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $dataInicio
     * @param string $dataFim
     * @return \Illuminate\Database\Eloquent\Builder
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
     * Filtra normas recentes (últimos X dias)
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecentes($query, $dias = 30)
    {
        return $query->where('data', '>=', now()->subDays($dias));
    }
    
    /**
     * Busca avançada com múltiplos critérios
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filtros
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuscaAvancada($query, array $filtros)
    {
        //Iniciar com filtro de status ativo
        $query->ativas();
        
        //Aplicar cada filtro se estiver definido
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
    
    /**
     * Filtra normas por publicidade
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $publicidadeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorPublicidade($query, $publicidadeId)
    {
        if (empty($publicidadeId)) {
            return $query;
        }
        
        return $query->where('publicidade_id', $publicidadeId);
    }
    
    /**
     * Filtra por usuário
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $usuarioId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        if (empty($usuarioId)) {
            return $query;
        }
        
        return $query->where('usuario_id', $usuarioId);
    }
}