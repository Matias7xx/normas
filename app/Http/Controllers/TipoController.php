<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTipoRequest;
use App\Models\Tipo;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TipoController extends Controller
{
  public function show(Request $request)
  {
    try {
      // Paginação (padrão 15)
      $perPage = $request->input('per_page', 15);
      $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;

      $tipo = Tipo::where('status', true)->orderBy('tipo')->paginate($perPage);

      // Manter parâmetros na URL da paginação
      $tipo->appends($request->only(['per_page']));

      return view('tipos.tipo_list', compact('tipo'));
    } catch (\Exception $e) {
      Log::error('Erro ao carregar tipos: ' . $e->getMessage());
      $tipo = collect([])->paginate(15);
      return view('tipos.tipo_list', compact('tipo'))->withErrors([
        'Erro ao carregar tipos: ' . $e->getMessage(),
      ]);
    }
  }

  public function create()
  {
    return view('tipos.tipo_create');
  }

  public function store(CreateTipoRequest $request)
  {
    try {
      Tipo::create([
        'usuario_id' => auth()->user()->id,
        'tipo' => $request->nome_tipo,
        'status' => true,
      ]);
      return redirect()
        ->route('tipos.tipo_list')
        ->withSuccess('Cadastro realizado com sucesso!');
    } catch (\Exception $e) {
      Log::error($e);
      return back()->withErrors([
        'Erro interno no servidor, informe o administrador do sistema!',
      ]);
    }
  }

  public function edit($id)
  {
    $tipo = Tipo::where('status', true)
      ->where('id', $id)
      ->orderBy('tipo')
      ->first();
    return view('tipos.tipo_edit', compact(['tipo']));
  }

  public function update(Request $request, $id)
  {
    try {
      $update_tipo = Tipo::find($id);
      $update_tipo->tipo = $request->nome_tipo;
      $update_tipo->save();
      return redirect()
        ->route('tipos.tipo_list')
        ->withSuccess('Edição realizada com sucesso!');
    } catch (\Exception $e) {
      Log::error($e);
      return back()->withErrors([
        'Erro interno no servidor, informe o administrador do sistema!',
      ]);
    }
  }

  /**
   * Remove o tipo especificado (soft delete)
   */
  public function destroy($id)
  {
    try {
      $tipo = Tipo::findOrFail($id);

      // Verificar se há normas usando este tipo
      $normasCount = \App\Models\Norma::where('tipo_id', $id)
        ->where('status', true)
        ->count();

      if ($normasCount > 0) {
        return redirect()
          ->route('tipos.tipo_list')
          ->withErrors([
            "Não é possível excluir este tipo. Existem {$normasCount} norma(s) utilizando este tipo.",
          ]);
      }

      // Log tentativa de exclusão
      Log::info(
        "Tentativa de exclusão de tipo - ID: {$id}, Nome: {$tipo->tipo}, Usuário: " .
          auth()->user()->name,
      );

      // Soft delete - marca como inativo
      $tipo->update(['status' => false]);

      Log::info("Tipo excluído com sucesso - ID: {$id}");

      return redirect()
        ->route('tipos.tipo_list')
        ->withSuccess('Tipo excluído com sucesso!');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      Log::warning("Tentativa de excluir tipo inexistente - ID: {$id}");
      return redirect()
        ->route('tipos.tipo_list')
        ->withErrors(['Tipo não encontrado.']);
    } catch (\Exception $e) {
      Log::error('Erro ao excluir tipo: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());

      return back()->withErrors(['Erro ao excluir tipo: ' . $e->getMessage()]);
    }
  }
}
