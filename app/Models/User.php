<?php

namespace App\Models;

use App\Models\Rh\Servidor;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
  use \OwenIt\Auditing\Auditable;
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'matricula',
    'cargo_id',
    'cargo',
    'classe_funcional',
    'nivel_funcional',
    'email',
    'password',
    'role_id',
    'cpf',
    'sexo',
    'active',
    'telefone',
    'status',
    'unidade_lotacao_id',
    'unidade_lotacao',
    'srpc',
    'dspc',
    'nivel',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ['password', 'remember_token'];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'active' => 'boolean',
  ];

  /**
   * Default attribute values
   */
  protected $attributes = [
    'active' => true,
    'cargo_id' => null,
    'role_id' => 4,
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $appends = ['cargo'];

  /**
   * Override getAttribute to handle missing fields safely
   */
  public function getAttribute($key)
  {
    // Handle potentially missing attributes with default values
    if (!array_key_exists($key, $this->attributes)) {
      switch ($key) {
        case 'cargo_id':
          return null;
        case 'status':
        case 'unidade_lotacao_id':
        case 'unidade_lotacao':
        case 'srpc':
        case 'dspc':
        case 'nivel':
        case 'classe_funcional':
        case 'nivel_funcional':
        case 'cpf':
        case 'sexo':
        case 'telefone':
          return null;
        case 'active':
          return true;
        case 'role_id':
          return 4;
        default:
          break;
      }
    }

    return parent::getAttribute($key);
  }

  /**
   * Relations
   */
  public function role()
  {
    return $this->belongsTo(Role::class);
  }

  public function servidor()
  {
    return $this->belongsTo(Servidor::class, 'matricula', 'matricula');
  }

  /**
   * Relacionamento com Boletins
   */
  public function boletins()
  {
    return $this->hasMany(Boletim::class, 'user_id');
  }

  /**
   * Relacionamento com Normas
   */
  public function normas()
  {
    return $this->hasMany(Norma::class, 'user_id');
  }

  /**
   * Relacionamento com Especificações
   */
  public function especificacoes()
  {
    return $this->hasMany(Especificacao::class, 'user_id');
  }

  /**
   * Scope para usuários ativos
   */
  public function scopeActive($query)
  {
    return $query->where('active', true);
  }

  /**
   * Verificar se usuário tem permissão de administrador
   */
  public function isAdmin(): bool
  {
    return in_array($this->role_id ?? 4, [1, 2]); // Root ou Admin
  }

  /**
   * Verificar se usuário pode gerenciar boletins
   */
  public function canManageBoletins(): bool
  {
    return in_array($this->role_id ?? 4, [1, 7]); // Root ou role específica para boletins
  }

  /**
   * Verificar se usuário é root
   */
  public function isRoot(): bool
  {
    return ($this->role_id ?? 4) === 1;
  }

  /**
   * Acessors
   */
  public function getCargoAttribute()
  {
    // Se já existe um valor salvo no campo 'cargo', usar ele (API)
    if (
      isset($this->attributes['cargo']) &&
      !empty($this->attributes['cargo']) &&
      $this->attributes['cargo'] !== 'Cargo Indefinido'
    ) {
      return $this->attributes['cargo'];
    }

    // Fallback para IDs numericos (código do cargo)
    $cargoId = $this->attributes['cargo_id'] ?? null;

    switch ($cargoId) {
      case '1':
      case 1:
        return 'Delegado de Polícia';
        break;

      case '2':
      case 2:
        return 'Investigador de Polícia';
        break;

      case '3':
      case 3:
        return 'Escrivão de Polícia';
        break;

      case '4':
      case 4:
        return 'Agente Operacional de Polícia';
        break;

      default:
        return 'Cargo Indefinido';
        break;
    }
  }

  /**
   * Accessor para matrícula formatada
   */
  public function getMatriculaFormatadaAttribute(): string
  {
    return str_pad($this->matricula ?? '', 7, '0', STR_PAD_LEFT);
  }

  /**
   * Accessor para CPF formatado
   */
  public function getCpfFormatadoAttribute(): string
  {
    if (!$this->cpf) {
      return '';
    }

    $cpf = preg_replace('/\D/', '', $this->cpf);
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
  }

  /**
   * Boot method para definir valores padrão
   */
  protected static function boot()
  {
    parent::boot();

    static::creating(function ($user) {
      if (!isset($user->active)) {
        $user->active = true;
      }
      if (!isset($user->role_id)) {
        $user->role_id = 4;
      }
    });
  }

  /**
   * Override toArray to ensure all expected fields exist
   */
  public function toArray()
  {
    $array = parent::toArray();

    // Ensure these fields exist even if null
    $expectedFields = [
      'cargo_id' => null,
      'cargo' => null,
      'cpf' => null,
      'sexo' => null,
      'telefone' => null,
      'classe_funcional' => null,
      'nivel_funcional' => null,
      'status' => null,
      'unidade_lotacao_id' => null,
      'unidade_lotacao' => null,
      'srpc' => null,
      'dspc' => null,
      'nivel' => null,
    ];

    foreach ($expectedFields as $field => $defaultValue) {
      if (!array_key_exists($field, $array)) {
        $array[$field] = $defaultValue;
      }
    }

    return $array;
  }
}
