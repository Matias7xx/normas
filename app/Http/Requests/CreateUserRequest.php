<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Gate::allows('add_users')) {
            return true;
          } else {
            return false;
          }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'name'          => 'required',
          'matricula'     => 'required|unique:users|max:8',
          'email'         => 'unique:users',
          'active'        => 'required',
          'password'      => 'required'
        ];
    }

    public function messages()
    {
     return [
       'name.required'         =>  'Informe o nome completo do usuário.',
       // 'email.required'        =>  'Informe o e-mail do usuário.',
       'email.email'           =>  'Informe um e-mail válido.',
       'email.unique'          =>  'Email já cadastrado no sistema para outro usuário.',
       'matricula.required'    =>  'Informe a matricula do usuário.',
       'matricula.unique'      =>  'Matrícula já cadastrada no sistema.',
       'matricula.max'         =>  'A matricula não pode ter mais que 8 caracteres.',
       'password.required'     =>  'Informe uma senha inicial para o usuário.'
     ];
    }
}
