<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNormaRequest;
use App\Models\Norma;
use App\Models\NormaChave;
use App\Models\Orgao;
use App\Models\PalavraChave;
use App\Models\Publicidade;
use App\Models\Rh\Servidor;
use App\Models\Tipo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// public function create($id)
//     {
//         $norma = Norma::with(['publicidade','orgao','tipo','palavrasChave'])
//             ->where('id', $id)
//             ->first();
//         return view('palavras_chaves.palavras_chaves_create', compact(['norma']));
//     }

class NormaController extends Controller
{
    public function edit($id)
    {
        $tipos = Tipo::orderBy('tipo')->get();
        $publicidades = Publicidade::orderBy('publicidade')->get();
        $orgaos = Orgao::where('status', true)
            ->orderBy('orgao')
            ->get();

        $palavra_chaves = PalavraChave::orderBy('palavra_chave')->get();

        $norma = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])
            ->where('id', $id)
            ->first();
        return view('normas.norma_edit', compact(['norma'], ['tipos'], ['orgaos'], ['publicidades'], ['palavra_chaves']));
    }

    public function create()
{
    $tipos = Tipo::orderBy('tipo')->get();
    $publicidades = Publicidade::orderBy('publicidade')->get();
    $orgaos = Orgao::where('status', true)
        ->orderBy('orgao')
        ->get();
    $palavras_chave = PalavraChave::where('status', true)
        ->orderBy('palavra_chave')
        ->get();
        
    return view('normas.norma_create', compact(['tipos'], ['orgaos'], ['publicidades'], ['palavras_chave']));
}

    public function index()
    {   $servidor = Servidor::where('matricula', Auth::user()->matricula)->first();
        $normas_por_tipo = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])->orderBy('tipo_id')->get()->groupBy('tipo.tipo');
        return view('normas.norma_list', compact(['normas_por_tipo', 'servidor']));
    }


    public function search(Request $request)
    {
        $tipo = Tipo::where('status', true)
            ->orderBy('tipo')
            ->get();

        $orgao = Orgao::where('status', true)
            ->orderBy('orgao')
            ->get();

        $query = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])->orderBy('tipo_id');

        if ($request->norma) {
            $query->where('descricao', 'ILIKE', "%{$request->norma}%");
        }
        if ($request->orgao) {
            $query->where('orgao_id', '=', $request->orgao);
        }
        if ($request->palavra_chave) {
            $query->whereHas('palavrasChave', function ($query) use ($request) {
                $query->where('palavra_chave', 'ILIKE', "%{$request->palavra_chave}%");
            });
        }
        if ($request->descricao && $request->descricao != '') {
            $termos = explode(' ', $request->descricao);
            foreach ($termos as $key => $t) {
                $query->whereRaw("remove_acento(descricao) ILIKE remove_acento('%" . $t . "%')");
            }
        }
        // dd($query->toSql());
        $norma_pesquisa = $query->get();
        return view('normas.norma_pesquisa', compact(['norma_pesquisa'], ['tipo'], ['orgao']));
    }

    public function store(CreateNormaRequest $request)
{
    try {
        /*função para salvar o arquivo*/
        if ($request->file('anexo')->isValid()) {
            /*cria um rash para renomear o arquivo*/
            $name_file = Str::uuid() . "." . $request->anexo->extension();
            /*salva o arquivo e renomeia automaticamente e renomeia com o valor informado */
            $request->file('anexo')->storeAs('public/normas', $name_file);
            
            // Criar a norma
            $norma = Norma::create([
                'usuario_id' => auth()->user()->id,
                'data' => $request->data,
                'descricao' => $request->descricao,
                'anexo' => $name_file,
                'resumo' => $request->resumo,
                'publicidade_id' => $request->publicidade,
                'tipo_id' => $request->tipo,
                'orgao_id' => $request->orgao,
                'status' => true
            ]);
            
            // Array para armazenar IDs de palavras-chave para vincular
            $palavrasChaveIds = [];
            
            // Processar palavras-chave existentes selecionadas
            if ($request->has('palavras_chave') && is_array($request->palavras_chave)) {
                $palavrasChaveIds = $request->palavras_chave;
            }
            
            // Processar novas palavras-chave
            if ($request->has('novas_palavras_chave') && !empty($request->novas_palavras_chave)) {
                try {
                    $novasPalavrasChave = json_decode($request->novas_palavras_chave, true);
                    
                    if (is_array($novasPalavrasChave) && count($novasPalavrasChave) > 0) {
                        foreach ($novasPalavrasChave as $palavraChave) {
                            // Verificar se esta palavra-chave já existe
                            $existente = PalavraChave::where('palavra_chave', 'ILIKE', $palavraChave)
                                ->where('status', true)
                                ->first();
                            
                            if ($existente) {
                                // Se existir, adicione o ID ao array
                                $palavrasChaveIds[] = $existente->id;
                            } else {
                                // Se não existir, crie-a e adicione o ID ao array
                                $novaPalavraChave = PalavraChave::create([
                                    'usuario_id' => auth()->user()->id,
                                    'palavra_chave' => $palavraChave,
                                    'status' => true
                                ]);
                                
                                $palavrasChaveIds[] = $novaPalavraChave->id;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao processar novas palavras-chave: ' . $e->getMessage());
                }
            }
            
            // Vincular todas as palavras-chave à norma
            if (!empty($palavrasChaveIds)) {
                foreach ($palavrasChaveIds as $palavraChaveId) {
                    NormaChave::create([
                        'norma_id' => $norma->id,
                        'palavra_chave_id' => $palavraChaveId,
                        'status' => true
                    ]);
                }
            }
            
            return redirect()->route('normas.norma_list')->withSuccess('Cadastro realizado com sucesso!');
        } else {
            return back()->withErrors(['Erro ao fazer o Upload do arquivo!']);
        }
    } catch (\Exception $e) {
        Log::error($e);
        return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema! ' . $e->getMessage()]);
    }
}

public function update(Request $request, $id)
{
    try {
        $tipos = Tipo::orderBy('tipo')->get();
        $publicidades = Publicidade::orderBy('publicidade')->get();
        $orgaos = Orgao::where('status', true)
            ->orderBy('orgao')
            ->get();
        $palavra_chaves = NormaChave::where('norma_id', '=', $id)
            ->where('status', true)
            ->get();
        $norma = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])
            ->where('id', $id)
            ->first();

        if (isset($request->delete_palavra_chave)) {
            //Soft delete - encontrar e atualizar status para false
            $normaChave = NormaChave::where('norma_id', $id)
                ->where('palavra_chave_id', $request->delete_palavra_chave)
                ->first();
                
            if ($normaChave) {
                $normaChave->status = false;
                $normaChave->save();
                return redirect()->route('normas.norma_edit', ['id' => $id])
                    ->withSuccess('Palavra-chave desvinculada com sucesso!');
            } else {
                return redirect()->route('normas.norma_edit', ['id' => $id])
                    ->withErrors(['Não foi possível desvincular a palavra-chave.']);
            }
        } elseif (isset($request->add_palavra_chave) || !empty($request->novas_palavras_chave)) {
            //Array para armazenar todos os IDs de palavras-chave para vincular
            $palavrasChaveIds = [];
            
            //Processar palavras-chave existentes selecionadas
            if ($request->has('add_palavra_chave') && is_array($request->add_palavra_chave)) {
                $palavrasChaveIds = $request->add_palavra_chave;
            }
            
            //Processar novas palavras-chave
            if ($request->has('novas_palavras_chave') && !empty($request->novas_palavras_chave)) {
                try {
                    $novasPalavrasChave = json_decode($request->novas_palavras_chave, true);
                    
                    if (is_array($novasPalavrasChave) && count($novasPalavrasChave) > 0) {
                        foreach ($novasPalavrasChave as $palavraChave) {
                            //Verificar se esta palavra-chave já existe
                            $existente = PalavraChave::where('palavra_chave', 'ILIKE', $palavraChave)
                                ->where('status', true)
                                ->first();
                            
                            if ($existente) {
                                //Se existir, adicione o ID ao array
                                $palavrasChaveIds[] = $existente->id;
                            } else {
                                //Se não existir, crie-a e adicione o ID ao array
                                $novaPalavraChave = PalavraChave::create([
                                    'usuario_id' => auth()->user()->id,
                                    'palavra_chave' => $palavraChave,
                                    'status' => true
                                ]);
                                
                                $palavrasChaveIds[] = $novaPalavraChave->id;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao processar novas palavras-chave: ' . $e->getMessage());
                }
            }
            
            //Vincular todas as palavras-chave à norma
            if (!empty($palavrasChaveIds)) {
                foreach ($palavrasChaveIds as $palavraChaveId) {
                    //Verificar se já existe um registro (mesmo que inativo)
                    $existente = NormaChave::where('norma_id', $id)
                        ->where('palavra_chave_id', $palavraChaveId)
                        ->first();
                        
                    if ($existente) {
                        //Se existir, apenas atualiza para ativo
                        $existente->status = true;
                        $existente->save();
                    } else {
                        //Se não existir, cria novo
                        NormaChave::create([
                            'norma_id' => $id,
                            'palavra_chave_id' => $palavraChaveId,
                            'status' => true
                        ]);
                    }
                }
            }
            
            return redirect()->route('normas.norma_edit', ['id' => $id])
                ->withSuccess('Palavras-chave vinculadas com sucesso!');
                
        } else {
            $update_norma = Norma::find($id);
            $update_norma->data  = $request->data;
            $update_norma->descricao = $request->descricao;
            if (($request->hasFile('anexo')) && ($request->file('anexo')->isValid())) {
                /*excluir arquivo anterior*/
                if(($update_norma->anexo != NULL)&&(file_exists(storage_path().'/app/public/normas/'.$update_norma->anexo))){
                    unlink(storage_path().'/app/public/normas/'.$update_norma->anexo);
                }
                /*cria um rash para renomear o arquivo*/
                $name_file = Str::uuid() . "." . $request->anexo->extension();
                /*salva o arquivo e renomeia automaticamente e renomeia com o valor informado */
                $request->file('anexo')->storeAs('public/normas', $name_file);
                $update_norma->anexo  = $name_file;
            }
            $update_norma->resumo = $request->resumo;
            $update_norma->publicidade_id  = $request->publicidade;
            $update_norma->tipo_id  = $request->tipo;
            $update_norma->orgao_id  = $request->orgao;
            $update_norma->save();
            
            //Processar as novas palavras-chave mesmo durante um save normal
            if ($request->has('novas_palavras_chave') && !empty($request->novas_palavras_chave)) {
                try {
                    $novasPalavrasChave = json_decode($request->novas_palavras_chave, true);
                    
                    if (is_array($novasPalavrasChave) && count($novasPalavrasChave) > 0) {
                        foreach ($novasPalavrasChave as $palavraChave) {
                            //Verificar se esta palavra-chave já existe
                            $existente = PalavraChave::where('palavra_chave', 'ILIKE', $palavraChave)
                                ->where('status', true)
                                ->first();
                            
                            if ($existente) {
                                //Verificar se já está vinculada à norma
                                $normaChaveExistente = NormaChave::where('norma_id', $id)
                                    ->where('palavra_chave_id', $existente->id)
                                    ->first();
                                    
                                if ($normaChaveExistente) {
                                    //Se existir, apenas atualiza para ativo
                                    $normaChaveExistente->status = true;
                                    $normaChaveExistente->save();
                                } else {
                                    //Se não existir, cria novo vínculo
                                    NormaChave::create([
                                        'norma_id' => $id,
                                        'palavra_chave_id' => $existente->id,
                                        'status' => true
                                    ]);
                                }
                            } else {
                                //Se não existir, crie-a e vincule
                                $novaPalavraChave = PalavraChave::create([
                                    'usuario_id' => auth()->user()->id,
                                    'palavra_chave' => $palavraChave,
                                    'status' => true
                                ]);
                                
                                NormaChave::create([
                                    'norma_id' => $id,
                                    'palavra_chave_id' => $novaPalavraChave->id,
                                    'status' => true
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao processar novas palavras-chave durante edição: ' . $e->getMessage());
                }
            }
            
            return redirect()->route('normas.norma_list')->withSuccess('Edição realizada com sucesso!');
        }
    } catch (\Exception $e) {
        Log::error($e);
        return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema! ' . $e->getMessage()]);
    }
}
}
