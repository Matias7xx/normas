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
use Illuminate\Support\Facades\DB;

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
    {   
        $servidor = Servidor::where('matricula', Auth::user()->matricula)->first();
        $normas_por_tipo = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])
            ->orderBy('tipo_id')
            ->get()
            ->groupBy('tipo.tipo');
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
        
        $norma_pesquisa = $query->get();
        return view('normas.norma_pesquisa', compact(['norma_pesquisa'], ['tipo'], ['orgao']));
    }

    public function store(CreateNormaRequest $request)
    {
        try {
            /*função para salvar o arquivo*/
            if ($request->file('anexo')->isValid()) {
                /*cria um hash para renomear o arquivo*/
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
            //Iniciar uma transação para garantir consistência dos dados
            DB::beginTransaction();
            
            //Obter a norma
            $norma = Norma::find($id);
            if (!$norma) {
                DB::rollBack();
                return back()->withErrors(['Norma não encontrada.']);
            }
            
            $mensagens = []; // Para armazenar mensagens de sucesso

            //Processar exclusão de palavra-chave (se solicitado)
            if (isset($request->delete_palavra_chave)) {
                $normaChave = NormaChave::where('norma_id', $id)
                    ->where('palavra_chave_id', $request->delete_palavra_chave)
                    ->first();
                    
                if ($normaChave) {
                    $normaChave->status = false;
                    $normaChave->save();
                    $mensagens[] = 'Palavra-chave desvinculada com sucesso!';
                }
            }
            
            //Processar adição de palavras-chave existentes e/ou novas
            $adicionouPalavras = false;
            
            // Processar palavras-chave existentes selecionadas
            if ($request->has('add_palavra_chave') && is_array($request->add_palavra_chave) && count($request->add_palavra_chave) > 0) {
                foreach ($request->add_palavra_chave as $palavraChaveId) {
                    // Verificar se já existe um registro (mesmo que inativo)
                    $existente = NormaChave::where('norma_id', $id)
                        ->where('palavra_chave_id', $palavraChaveId)
                        ->first();
                        
                    if ($existente) {
                        // Se existir, apenas atualiza para ativo
                        if (!$existente->status) {
                            $existente->status = true;
                            $existente->save();
                            $adicionouPalavras = true;
                        }
                    } else {
                        // Se não existir, cria novo
                        NormaChave::create([
                            'norma_id' => $id,
                            'palavra_chave_id' => $palavraChaveId,
                            'status' => true
                        ]);
                        $adicionouPalavras = true;
                    }
                }
            }
            
            // Processar novas palavras-chave
            if ($request->has('novas_palavras_chave') && !empty($request->novas_palavras_chave)) {
                $novasPalavrasChave = json_decode($request->novas_palavras_chave, true);
                
                if (is_array($novasPalavrasChave) && count($novasPalavrasChave) > 0) {
                    foreach ($novasPalavrasChave as $palavraChave) {
                        // Verificar se esta palavra-chave já existe
                        $existente = PalavraChave::where('palavra_chave', 'ILIKE', $palavraChave)
                            ->where('status', true)
                            ->first();
                        
                        if ($existente) {
                            // Verificar se já está vinculada à norma
                            $normaChaveExistente = NormaChave::where('norma_id', $id)
                                ->where('palavra_chave_id', $existente->id)
                                ->first();
                                
                            if ($normaChaveExistente) {
                                // Se existir, apenas atualiza para ativo
                                if (!$normaChaveExistente->status) {
                                    $normaChaveExistente->status = true;
                                    $normaChaveExistente->save();
                                    $adicionouPalavras = true;
                                }
                            } else {
                                // Se não existir, cria novo vínculo
                                NormaChave::create([
                                    'norma_id' => $id,
                                    'palavra_chave_id' => $existente->id,
                                    'status' => true
                                ]);
                                $adicionouPalavras = true;
                            }
                        } else {
                            // Se não existir, crie e vincule
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
                            $adicionouPalavras = true;
                        }
                    }
                }
            }
            
            if ($adicionouPalavras) {
                $mensagens[] = 'Palavras-chave vinculadas com sucesso!';
            }
            
            //Atualizar dados da norma
            $atualizouNorma = false;
            
            if ($request->has('data')) {
                $norma->data = $request->data;
                $atualizouNorma = true;
            }
            
            if ($request->has('descricao')) {
                $norma->descricao = $request->descricao;
                $atualizouNorma = true;
            }
            
            if ($request->has('resumo')) {
                $norma->resumo = $request->resumo;
                $atualizouNorma = true;
            }
            
            if ($request->has('publicidade')) {
                $norma->publicidade_id = $request->publicidade;
                $atualizouNorma = true;
            }
            
            if ($request->has('tipo')) {
                $norma->tipo_id = $request->tipo;
                $atualizouNorma = true;
            }
            
            if ($request->has('orgao')) {
                $norma->orgao_id = $request->orgao;
                $atualizouNorma = true;
            }
            
            //Processar upload de arquivo (se enviado)
            if (($request->hasFile('anexo')) && ($request->file('anexo')->isValid())) {
                // Excluir arquivo anterior
                if (($norma->anexo != NULL) && (file_exists(storage_path().'/app/public/normas/'.$norma->anexo))) {
                    unlink(storage_path().'/app/public/normas/'.$norma->anexo);
                }
                
                // Criar um hash para renomear o arquivo
                $name_file = Str::uuid() . "." . $request->anexo->extension();
                
                //Salvar o arquivo com o novo nome
                $request->file('anexo')->storeAs('public/normas', $name_file);
                $norma->anexo = $name_file;
                $atualizouNorma = true;
            }
            
            // Salvar a norma se algo foi alterado
            if ($atualizouNorma) {
                $norma->save();
                $mensagens[] = 'Informações da norma atualizadas com sucesso!';
            }
            
            //Confirmar todas as operações
            DB::commit();
            
            //Determinar mensagem de sucesso
            if (count($mensagens) > 0) {
                $mensagemFinal = implode(' ', $mensagens);
            } else {
                $mensagemFinal = 'Nenhuma alteração foi realizada.';
            }
            
            // Redirecionar de acordo com o tipo de operação
            if (isset($request->delete_palavra_chave) || isset($request->add_palavra_chave) || !empty($request->novas_palavras_chave)) {
                //Se foi uma operação em palavras-chave, redireciona de volta para a tela de edição
                return redirect()->route('normas.norma_edit', ['id' => $id])->withSuccess($mensagemFinal);
            } else {
                //Se foi uma edição normal, redireciona para a lista
                return redirect()->route('normas.norma_list')->withSuccess($mensagemFinal);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema! ' . $e->getMessage()]);
        }
    }
}