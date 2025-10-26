<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Boletim extends Model
{
  use HasFactory;

  protected $table = 'boletins';

  protected $fillable = [
    'nome',
    'descricao',
    'data_publicacao',
    'arquivo',
    'status',
    'user_id',
  ];

  protected $casts = [
    'data_publicacao' => 'date',
    'status' => 'boolean',
  ];

  /**
   * Boot method para definir valores padrão quando criando via Eloquent
   */
  protected static function boot()
  {
    parent::boot();

    static::creating(function ($boletim) {
      // Define user_id como usuário autenticado se não especificado
      if (empty($boletim->user_id) && auth()->check()) {
        $boletim->user_id = auth()->id();
      }

      // Define data_publicacao como hoje se não especificada
      if (empty($boletim->data_publicacao)) {
        $boletim->data_publicacao = Carbon::today();
      }
    });
  }

  /**
   * Relacionamento com usuário que cadastrou
   */
  public function usuario(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Scope para boletins ativos
   */
  public function scopeAtivos($query)
  {
    return $query->where('status', true);
  }

  /**
   * Scope para ordenar por data de publicação (mais recente primeiro)
   */
  public function scopeOrdenado($query)
  {
    return $query->orderBy('data_publicacao', 'desc');
  }

  /**
   * Accessor para formatação da data
   */
  public function getDataPublicacaoFormatadaAttribute()
  {
    return $this->data_publicacao->format('d/m/Y');
  }

  /**
   * Accessor para nome do arquivo sanitizado
   */
  public function getNomeArquivoDownloadAttribute()
  {
    return $this->sanitize_filename($this->nome) . '.pdf';
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
