<?php

namespace App\Http\Requests;

use App\Models\Norma;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateNormaRequest extends FormRequest
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
            'data' => 'required|date',
            'publicidade' => 'required|exists:publicidades,id',
            'tipo' => 'required|exists:tipos,id',
            'orgao' => 'required|exists:orgaos,id',
            'anexo' => 'required|file|mimes:pdf|max:20480', // 20MB máximo
            'descricao' => 'required|string|min:10|max:255',
            'resumo' => 'required|string|min:10|max:1000',
            'vigente' => 'required|in:' . implode(',', array_keys(Norma::getVigenteOptions())),
            'palavras_chave' => 'nullable|array',
            'palavras_chave.*' => 'exists:palavra_chaves,id',
            'novas_palavras_chave' => 'nullable|string'
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
            // Verifica se pelo menos uma das opções de palavras-chave foi preenchida
            $palavrasChaveExistentes = $this->input('palavras_chave', []);
            $novasPalavrasChave = $this->input('novas_palavras_chave', '');

            // Limpa string de novas palavras chave (remove espaços vazios)
            $novasPalavrasChaveLimpa = trim($novasPalavrasChave);

            // Se não tem palavras existentes selecionadas E não tem novas palavras
            if (empty($palavrasChaveExistentes) && empty($novasPalavrasChaveLimpa)) {
                $validator->errors()->add(
                    'palavras_chave', 
                    'É obrigatório selecionar pelo menos uma palavra-chave existente ou criar uma nova palavra-chave.'
                );
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
            
            'anexo.required' => 'O campo Anexo é obrigatório.',
            'anexo.file' => 'O anexo deve ser um arquivo válido.',
            'anexo.mimes' => 'O anexo deve ser um arquivo PDF.',
            'anexo.max' => 'O anexo não pode ser maior que 20MB.',
            
            'descricao.required' => 'O campo Descrição é obrigatório.',
            'descricao.min' => 'A descrição deve ter pelo menos 10 caracteres.',
            'descricao.max' => 'A descrição não pode ter mais de 255 caracteres.',
            
            'resumo.required' => 'O campo Resumo é obrigatório.',
            'resumo.min' => 'O resumo deve ter pelo menos 10 caracteres.',
            'resumo.max' => 'O resumo não pode ter mais de 1000 caracteres.',
            
            'vigente.required' => 'O campo Status de Vigência é obrigatório.',
            'vigente.in' => 'O status de vigência selecionado é inválido.',
            
            'palavras_chave.array' => 'As palavras-chave devem ser um array.',
            'palavras_chave.*.exists' => 'Uma ou mais palavras-chave selecionadas são inválidas.',
            
            'novas_palavras_chave.string' => 'As novas palavras-chave devem ser um texto válido.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Se vigente não foi informado, definir como VIGENTE por padrão
        if (!$this->has('vigente') || empty($this->vigente)) {
            $this->merge([
                'vigente' => Norma::VIGENTE_VIGENTE
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
            'palavras_chave' => 'palavras-chave',
            'novas_palavras_chave' => 'novas palavras-chave'
        ];
    }
}