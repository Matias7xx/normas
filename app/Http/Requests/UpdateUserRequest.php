<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    // Se for usuário root (role_id = 1), sempre autorizar
    if (Auth::user()->role_id == 1) {
      return true;
    }

    // Para outros usuários, verificar a permissão edit_users
    return Gate::allows('edit_users');
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    $userId = $this->route('id') ?? $this->route('user'); // Flexibilidade no nome da rota

    return [
      'name' => 'required|string|max:255',
      'matricula' => [
        'required',
        'max:8',
        Rule::unique('users', 'matricula')->ignore($userId),
      ],
      'email' => [
        'nullable',
        'email',
        Rule::unique('users', 'email')->ignore($userId),
      ],
      'active' => 'required|boolean',
      'password' => 'nullable|string|min:6', // Senha opcional na edição
      'role_id' => 'required|exists:roles,id',
      'cargo_id' => 'nullable|in:1,2,3,4',
      'cpf' => 'nullable|string|max:14',
      'telefone' => 'nullable|string|max:15',
    ];
  }

  /**
   * Custom validation messages
   *
   * @return array
   */
  public function messages()
  {
    return [
      'name.required' => 'Informe o nome completo do usuário.',
      'name.max' => 'O nome não pode ter mais que 255 caracteres.',
      'email.email' => 'Informe um e-mail válido.',
      'email.unique' => 'Email já cadastrado no sistema para outro usuário.',
      'matricula.required' => 'Informe a matrícula do usuário.',
      'matricula.unique' => 'Matrícula já cadastrada no sistema.',
      'matricula.max' => 'A matrícula não pode ter mais que 8 caracteres.',
      'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
      'role_id.required' => 'Selecione um perfil para o usuário.',
      'role_id.exists' => 'O perfil selecionado não existe.',
      'active.required' => 'Informe o status do usuário.',
      'active.boolean' => 'O status deve ser Ativo ou Inativo.',
      'cargo_id.in' => 'Selecione um cargo válido.',
    ];
  }

  /**
   * Prepare the data for validation.
   *
   * @return void
   */
  protected function prepareForValidation()
  {
    // Garantir que telefone seja salvo corretamente
    if ($this->has('phone')) {
      $this->merge([
        'telefone' => $this->phone,
      ]);
    }

    // Converter active para boolean se necessário
    if ($this->has('active')) {
      $this->merge([
        'active' => (bool) $this->active,
      ]);
    }
  }
}
