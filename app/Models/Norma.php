<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Norma extends Model
{
    use HasFactory;

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

    /*relacionamentos*/
    public function publicidade(){
        return $this->belongsTo(Publicidade::class, 'publicidade_id', 'id')->select('id','publicidade');
    }

    public function orgao(){
        return $this->belongsTo(Orgao::class, 'orgao_id', 'id')->select('id','orgao');
    }

    public function tipo(){
        return $this->belongsTo(Tipo::class, 'tipo_id', 'id')->select('id','tipo');
    }

    public function palavrasChave(){
        return $this->belongsToMany(PalavraChave::class, 'normas_chaves', 'norma_id', 'palavra_chave_id')
                    ->wherePivot('status', true);
    }

}


