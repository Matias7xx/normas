<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoDuplicadaVerificado extends Model
{
  protected $table = 'grupos_duplicadas_verificados';

  protected $fillable = [
    'hash_grupo',
    'normas_ids',
    'verificado_por',
    'verificado_em',
    'observacoes',
    'status',
  ];

  protected $casts = [
    'normas_ids' => 'array',
    'verificado_em' => 'datetime',
  ];

  /**
   * Usuário que verificou o grupo
   */
  public function verificadoPor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'verificado_por');
  }

  /**
   * Gera hash para um grupo de normas
   */
  public static function gerarHashGrupo(array $normasIds): string
  {
    sort($normasIds); // Ordenar para garantir consistência
    return hash('sha256', implode('-', $normasIds));
  }

  /**
   * Verifica se um grupo já foi verificado
   */
  public static function grupoJaVerificado(array $normasIds): bool
  {
    $hash = self::gerarHashGrupo($normasIds);
    return self::where('hash_grupo', $hash)->exists();
  }

  /**
   * Marca um grupo como verificado
   */
  public static function marcarComoVerificado(
    array $normasIds,
    int $usuarioId,
    string $status = 'verificado',
    ?string $observacoes = null
  ): self {
    $hash = self::gerarHashGrupo($normasIds);

    return self::create([
      'hash_grupo' => $hash,
      'normas_ids' => $normasIds,
      'verificado_por' => $usuarioId,
      'verificado_em' => now(),
      'observacoes' => $observacoes,
      'status' => $status,
    ]);
  }
}
