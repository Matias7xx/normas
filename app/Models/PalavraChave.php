<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalavraChave extends Model
{
    use HasFactory;

    protected $table = 'palavra_chaves';

    protected $fillable = [
        'usuario_id',
        'palavra_chave',
        'status'
    ];
    
    /**
     * Retorna todas as normas associadas a esta palavra-chave
     */
    public function normas()
    {
        return $this->belongsToMany(
            Norma::class,
            'normas_chaves',
            'palavra_chave_id',
            'norma_id'
        );
    }
    
    /**
     * Retorna apenas normas ativas associadas a esta palavra-chave com relação ativa
     */
    public function normasAtivas()
    {
        return $this->belongsToMany(
            Norma::class,
            'normas_chaves',
            'palavra_chave_id',
            'norma_id'
        )
        ->where('normas.status', true)
        ->wherePivot('status', true);
    }
    
    /**
     * Retorna o usuário que criou a palavra-chave
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
    
    /**
     * Verifica se a palavra-chave tem normas associadas
     * 
     * @return bool
     */
    public function temNormasVinculadas()
    {
        return $this->normasAtivas()->count() > 0;
    }
}