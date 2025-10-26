<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEspecificacaoRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return auth()->check();
  }

  /**
   * Get the validation rules that apply to the request.
   */
  public function rules(): array
  {
    return [
      'nome' => [
        'required',
        'string',
        'max:255',
        Rule::unique('especificacoes')
          ->ignore($this->route('id'))
          ->where(function ($query) {
            return $query->where('status', true);
          }),
      ],
      'arquivo' => 'nullable|file|mimes:pdf|max:10240', // 10MB, opcional para update
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   */
  public function messages(): array
  {
    return [
      'nome.required' => 'O nome da especificação é obrigatório.',
      'nome.string' => 'O nome deve ser um texto válido.',
      'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
      'nome.unique' => 'Já existe uma especificação com este nome.',

      'arquivo.file' => 'O arquivo enviado não é válido.',
      'arquivo.mimes' => 'O arquivo deve ser um PDF.',
      'arquivo.max' => 'O arquivo não pode ser maior que 10MB.',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   */
  public function attributes(): array
  {
    return [
      'nome' => 'nome da especificação',
      'arquivo' => 'arquivo PDF',
    ];
  }

  /**
   * Configure the validator instance.
   */
  public function withValidator($validator)
  {
    $validator->after(function ($validator) {
      // Se não há arquivo atual e não está enviando um novo arquivo, é obrigatório
      $especificacao = \App\Models\Especificacao::find($this->route('id'));

      if (
        $especificacao &&
        !$especificacao->arquivo &&
        !$this->hasFile('arquivo')
      ) {
        $validator
          ->errors()
          ->add(
            'arquivo',
            'O arquivo PDF é obrigatório quando não há arquivo cadastrado.',
          );
      }
    });
  }
}
