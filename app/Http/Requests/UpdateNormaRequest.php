<?php

namespace App\Http\Requests;

use App\Models\Norma;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateNormaRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'data' => 'sometimes|required|date',
      'publicidade' => 'sometimes|required|exists:publicidades,id',
      'tipo' => 'sometimes|required|exists:tipos,id',
      'orgao' => 'sometimes|required|exists:orgaos,id',
      'anexo' => 'sometimes|file|mimes:pdf|max:30720', // 30MB máximo
      'descricao' => 'sometimes|required|string|min:10|max:255',
      'resumo' => 'sometimes|required|string|min:10|max:1000',
      'vigente' =>
        'sometimes|required|in:' .
        implode(',', array_keys(Norma::getVigenteOptions())),

      'vigencia_indeterminada' => 'sometimes|boolean',
      'data_limite_vigencia' => 'nullable|date|after:today',

      // Palavras-chave para adicionar
      'add_palavra_chave' => 'nullable|array',
      'add_palavra_chave.*' => 'exists:palavra_chaves,id',
      'novas_palavras_chave' => 'nullable|string',

      // Palavra-chave para excluir
      'delete_palavra_chave' => 'nullable|exists:palavra_chaves,id',
    ];
  }

  /**
   * Configure the validator instance.
   *
   * @param  \Illuminate\Validation\Validator  $validator
   * @return void
   */
  public function withValidator(Validator $validator)
  {
    $validator->after(function ($validator) {
      // Validar se está tentando excluir e adicionar a mesma palavra-chave
      if (
        $this->has('delete_palavra_chave') &&
        $this->has('add_palavra_chave')
      ) {
        $palavraChaveParaExcluir = $this->input('delete_palavra_chave');
        $palavrasChaveParaAdicionar = $this->input('add_palavra_chave', []);

        if (in_array($palavraChaveParaExcluir, $palavrasChaveParaAdicionar)) {
          $validator
            ->errors()
            ->add(
              'add_palavra_chave',
              'Não é possível adicionar e excluir a mesma palavra-chave simultaneamente.',
            );
        }
      }

      // Validação de vigência com data limite (apenas se os campos foram enviados)
      if (
        $this->has('vigencia_indeterminada') ||
        $this->has('data_limite_vigencia')
      ) {
        $vigenciaIndeterminada = $this->input('vigencia_indeterminada');
        $dataLimiteVigencia = $this->input('data_limite_vigencia');

        // Se vigência foi definida como não indeterminada (false), data limite é obrigatória
        if (
          $this->has('vigencia_indeterminada') &&
          $vigenciaIndeterminada === false &&
          empty($dataLimiteVigencia)
        ) {
          $validator
            ->errors()
            ->add(
              'data_limite_vigencia',
              'A data limite é obrigatória quando a vigência não é indeterminada.',
            );
        }

        // Se vigência é indeterminada (true), não deve ter data limite
        if (
          $this->has('vigencia_indeterminada') &&
          $vigenciaIndeterminada === true &&
          !empty($dataLimiteVigencia)
        ) {
          $validator
            ->errors()
            ->add(
              'data_limite_vigencia',
              'Não é possível definir data limite quando a vigência é indeterminada.',
            );
        }
      }
    });
  }

  /**
   * Get custom messages for validator errors.
   *
   * @return array
   */
  public function messages()
  {
    return [
      'data.required' => 'O campo Data é obrigatório.',
      'data.date' => 'O campo Data deve ser uma data válida.',

      'publicidade.required' => 'O campo Publicidade é obrigatório.',
      'publicidade.exists' => 'A publicidade selecionada é inválida.',

      'tipo.required' => 'O campo Tipo de norma é obrigatório.',
      'tipo.exists' => 'O tipo de norma selecionado é inválido.',

      'orgao.required' => 'O campo Órgão é obrigatório.',
      'orgao.exists' => 'O órgão selecionado é inválido.',

      'anexo.file' => 'O anexo deve ser um arquivo válido.',
      'anexo.mimes' => 'O anexo deve ser um arquivo PDF.',
      'anexo.max' => 'O anexo não pode ser maior que 30MB.',

      'descricao.required' => 'O campo Descrição é obrigatório.',
      'descricao.min' => 'A descrição deve ter pelo menos 10 caracteres.',
      'descricao.max' => 'A descrição não pode ter mais de 255 caracteres.',

      'resumo.required' => 'O campo Resumo é obrigatório.',
      'resumo.min' => 'O resumo deve ter pelo menos 10 caracteres.',
      'resumo.max' => 'O resumo não pode ter mais de 1000 caracteres.',

      'vigente.required' => 'O campo Status de Vigência é obrigatório.',
      'vigente.in' => 'O status de vigência selecionado é inválido.',

      'vigencia_indeterminada.boolean' =>
        'O campo vigência indeterminada deve ser verdadeiro ou falso.',
      'data_limite_vigencia.date' => 'A data limite deve ser uma data válida.',
      'data_limite_vigencia.after' =>
        'A data limite deve ser posterior a hoje.',

      'add_palavra_chave.array' => 'As palavras-chave devem ser um array.',
      'add_palavra_chave.*.exists' =>
        'Uma ou mais palavras-chave selecionadas são inválidas.',

      'novas_palavras_chave.string' =>
        'As novas palavras-chave devem ser um texto válido.',

      'delete_palavra_chave.exists' =>
        'A palavra-chave selecionada para exclusão é inválida.',
    ];
  }

  /**
   * Prepare the data for validation.
   *
   * @return void
   */
  protected function prepareForValidation()
  {
    //verificar se existe o campo hidden
    $vigenciaValue = null;

    if ($this->has('vigencia_indeterminada_hidden')) {
      // Usar o valor do campo hidden
      $vigenciaValue = $this->input('vigencia_indeterminada_hidden');
    } elseif ($this->has('vigencia_indeterminada')) {
      // Fallback para o checkbox original
      $vigenciaValue = $this->input('vigencia_indeterminada');
    }

    if ($vigenciaValue !== null) {
      // Converter para boolean
      if (is_string($vigenciaValue)) {
        $vigenciaValue =
          $vigenciaValue === '1' ||
          $vigenciaValue === 'true' ||
          $vigenciaValue === 'on';
      } elseif (is_numeric($vigenciaValue)) {
        $vigenciaValue = (bool) intval($vigenciaValue);
      }

      $this->merge([
        'vigencia_indeterminada' => $vigenciaValue,
      ]);
    }
  }

  /**
   * Get custom attributes for validator errors.
   *
   * @return array
   */
  public function attributes()
  {
    return [
      'data' => 'data',
      'publicidade' => 'publicidade',
      'tipo' => 'tipo de norma',
      'orgao' => 'órgão',
      'anexo' => 'anexo',
      'descricao' => 'descrição',
      'resumo' => 'resumo',
      'vigente' => 'status de vigência',
      'vigencia_indeterminada' => 'vigência indeterminada',
      'data_limite_vigencia' => 'data limite de vigência',
      'add_palavra_chave' => 'palavras-chave para adicionar',
      'novas_palavras_chave' => 'novas palavras-chave',
      'delete_palavra_chave' => 'palavra-chave para excluir',
    ];
  }
}
