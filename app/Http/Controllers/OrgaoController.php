<?php

namespace App\Http\Controllers;
use App\Http\Requests\CreateOrgaoRequest;
use App\Models\Orgao;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class OrgaoController extends Controller
{
    public function edit($id)
    {
        $orgao = Orgao::where('status', true)
            ->where('id', $id)
            ->orderBy('orgao')
            ->first();
        return view('orgaos.orgao_edit', compact(['orgao']));
    }

    public function create()
    {
        return view('orgaos.orgao_create');
    }

    public function show(Request $request)
    {
        try {
            // Paginação (padrão 15)
            $perPage = $request->input('per_page', 15);
            $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;

            $orgao = Orgao::where('status', true)
                ->orderBy('orgao')
                ->paginate($perPage);

            // Manter parâmetros na URL da paginação
            $orgao->appends($request->only(['per_page']));

            return view('orgaos.orgao_list', compact('orgao'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar órgãos: ' . $e->getMessage());
            $orgao = collect([])->paginate(15);
            return view('orgaos.orgao_list', compact('orgao'))
                ->withErrors(['Erro ao carregar órgãos: ' . $e->getMessage()]);
        }
    }

    public function store(CreateOrgaoRequest $request)
    {
        try {
            Orgao::create([
                // 'rash' => $request->_token,
                'usuario_id' => auth()->user()->id,
                'orgao' => $request->nome_orgao,
                'status' => true
            ]);
            return redirect()->route('orgaos.orgao_list')->withSuccess('Cadastro realizado com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $update_orgao = Orgao::find($id);
            $update_orgao->orgao  = $request->nome_orgao;
            $update_orgao->save();
            return redirect()->route('orgaos.orgao_list')->withSuccess('Edição realizada com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    /**
 * Remove o órgão especificado (soft delete)
 */
    public function destroy($id)
    {
        try {
            $orgao = Orgao::findOrFail($id);
            
            // Verificar se há normas usando este órgão
            $normasCount = \App\Models\Norma::where('orgao_id', $id)
                ->where('status', true)
                ->count();
            
            if ($normasCount > 0) {
                return redirect()->route('orgaos.orgao_list')
                    ->withErrors(["Não é possível excluir este órgão. Existem {$normasCount} norma(s) utilizando este órgão."]);
            }
            
            // Log da tentativa de exclusão
            Log::info("Tentativa de exclusão de órgão - ID: {$id}, Nome: {$orgao->orgao}, Usuário: " . auth()->user()->name);
            
            // Soft delete - marca como inativo
            $orgao->update(['status' => false]);
            
            Log::info("Órgão excluído com sucesso - ID: {$id}");

            return redirect()->route('orgaos.orgao_list')
                ->withSuccess('Órgão excluído com sucesso!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("Tentativa de excluir órgão inexistente - ID: {$id}");
            return redirect()->route('orgaos.orgao_list')
                ->withErrors(['Órgão não encontrado.']);
                
        } catch (\Exception $e) {
            Log::error('Erro ao excluir órgão: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->withErrors(['Erro ao excluir órgão: ' . $e->getMessage()]);
        }
    }
}
