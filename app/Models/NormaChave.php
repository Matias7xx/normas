<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NormaChave extends Model
{
    use HasFactory;

    protected $table = 'normas_chaves';

    protected $fillable = [
        'norma_id',
        'palavra_chave_id',
        'status'
    ];
}
