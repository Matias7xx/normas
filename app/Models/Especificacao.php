<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especificacao extends Model
{
  use HasFactory;

  protected $table = 'especificacoes';

  protected $fillable = ['nome', 'arquivo', 'status', 'usuario_id'];

  protected $casts = [
    'status' => 'boolean',
  ];

  /**
   * Relacionamento com usuário
   */
  public function usuario()
  {
    return $this->belongsTo(User::class, 'usuario_id');
  }

  /**
   * Scope para especificações ativas
   */
  public function scopeAtivas($query)
  {
    return $query->where('status', true);
  }

  /**
   * Accessor para URL do arquivo
   */
  public function getArquivoUrlAttribute()
  {
    if ($this->arquivo) {
      return asset('storage/especificacoes/' . $this->arquivo);
    }
    return null;
  }
}
