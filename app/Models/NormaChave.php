<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NormaChave extends Model
{
  use HasFactory;

  /**
   * Nome da tabela
   * @var string
   */
  protected $table = 'normas_chaves';

  /**
   * Atributos que são atribuíveis em massa.
   * @var array
   */
  protected $fillable = ['norma_id', 'palavra_chave_id', 'status'];

  /**
   * Os atributos que devem ser convertidos para tipos nativos.
   *
   * @var array
   */
  protected $casts = [
    'status' => 'boolean',
  ];

  /**
   * Relacionamentos
   */

  /**
   * Retorna a norma relacionada
   */
  public function norma()
  {
    return $this->belongsTo(Norma::class, 'norma_id');
  }

  /**
   * Retorna a palavra-chave relacionada
   */
  public function palavraChave()
  {
    return $this->belongsTo(PalavraChave::class, 'palavra_chave_id');
  }

  /**
   * Scopes
   */

  /**
   * Filtra apenas relacionamentos ativos
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeAtivos($query)
  {
    return $query->where('status', true);
  }

  /**
   * Filtra por norma
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param int $normaId
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopePorNorma($query, $normaId)
  {
    return $query->where('norma_id', $normaId);
  }

  /**
   * Filtra por palavra-chave
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param int $palavraChaveId
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopePorPalavraChave($query, $palavraChaveId)
  {
    return $query->where('palavra_chave_id', $palavraChaveId);
  }

  /**
   * Filtra por normas ativas e palavras-chave ativas
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeRelacoesAtivas($query)
  {
    return $query
      ->ativos()
      ->whereHas('norma', function ($q) {
        $q->where('status', true);
      })
      ->whereHas('palavraChave', function ($q) {
        $q->where('status', true);
      });
  }

  /**
   * Obtém as palavras-chave mais usadas
   * @param int $limit
   * @return \Illuminate\Support\Collection
   */
  public static function palavrasChaveMaisUsadas($limit = 10)
  {
    return self::select('palavra_chave_id')
      ->selectRaw('count(*) as total')
      ->where('status', true)
      ->groupBy('palavra_chave_id')
      ->orderByRaw('count(*) DESC')
      ->limit($limit)
      ->with('palavraChave:id,palavra_chave')
      ->get();
  }

  /**
   * Obtém as normas que compartilham as mesmas palavras-chave
   * @param int $normaId
   * @param int $limit
   * @return \Illuminate\Support\Collection
   */
  public static function normasRelacionadas($normaId, $limit = 5)
  {
    // Buscar palavras-chave da norma atual
    $palavrasChaveIds = self::where('norma_id', $normaId)
      ->where('status', true)
      ->pluck('palavra_chave_id');

    if ($palavrasChaveIds->isEmpty()) {
      return collect();
    }

    // Buscar normas que compartilham estas palavras-chave
    return Norma::whereHas('palavrasChave', function ($query) use (
      $palavrasChaveIds
    ) {
      $query->whereIn('palavras_chaves.id', $palavrasChaveIds);
    })
      ->where('id', '!=', $normaId)
      ->where('status', true)
      ->orderBy('data', 'desc')
      ->limit($limit)
      ->get();
  }
}
