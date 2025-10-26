<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publicidade extends Model
{
  use HasFactory;

  public function documento()
  {
    // return $this->belongsTo(Documento::class, 'publicidade_id', 'id');
  }
}
