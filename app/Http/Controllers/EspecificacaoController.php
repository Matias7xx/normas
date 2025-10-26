<?php

namespace App\Http\Controllers;

use App\Models\Especificacao;
use App\Http\Requests\CreateEspecificacaoRequest;
use App\Http\Requests\UpdateEspecificacaoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\StorageHelper;

class EspecificacaoController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    try {
      // Paginação (padrão 15)
      $perPage = $request->input('per_page', 15);
      $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;

      $especificacoes = Especificacao::where('status', true)
        ->with('usuario')
        ->orderBy('nome')
        ->paginate($perPage);

      // Manter parâmetros na URL da paginação
      $especificacoes->appends($request->only(['per_page']));

      return view(
        'especificacoes.especificacoes_list',
        compact('especificacoes'),
      );
    } catch (\Exception $e) {
      Log::error('Erro ao carregar especificações: ' . $e->getMessage());
      $especificacoes = Especificacao::where('status', true)->paginate(
        $perPage,
      );
      return view(
        'especificacoes.especificacoes_list',
        compact('especificacoes'),
      )->withErrors(['Erro ao carregar especificações: ' . $e->getMessage()]);
    }
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return view('especificacoes.especificacoes_create');
  }

  /**
   * Sanitiza o nome do arquivo baseado no nome da especificação
   *
   * @param string $nome
   * @param string $extension
   * @return string
   */
  private function generateEspecificacaoFileName($nome, $extension)
  {
    // Remover caracteres especiais e limitar tamanho
    $nomeArquivo = preg_replace('/[^A-Za-z0-9\s\-_.]/', '', $nome);

    // Substituir múltiplos espaços por um só e converter para underscore
    $nomeArquivo = preg_replace('/\s+/', '_', trim($nomeArquivo));

    // Limitar o tamanho do nome (máximo 100 caracteres)
    $nomeArquivo = substr($nomeArquivo, 0, 100);

    // Remover underscores do final
    $nomeArquivo = rtrim($nomeArquivo, '_');

    // Se ficou vazio, usar fallback
    if (empty($nomeArquivo)) {
      $nomeArquivo = 'especificacao_' . date('Y_m_d_His');
    }

    return $nomeArquivo . '.' . $extension;
  }

  /**
   * Verifica se já existe um arquivo com o mesmo nome no MinIO
   * Se existir, adiciona um sufixo numérico
   *
   * @param string $fileName
   * @return string
   */
  private function getUniqueEspecificacaoFileName($fileName)
  {
    $originalName = pathinfo($fileName, PATHINFO_FILENAME);
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    $counter = 1;

    // Verificar se o arquivo já existe
    while (StorageHelper::especificacoes()->exists($fileName)) {
      $fileName = $originalName . '_' . $counter . '.' . $extension;
      $counter++;
    }

    return $fileName;
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(CreateEspecificacaoRequest $request)
  {
    try {
      $nomeArquivo = null;

      // Upload do arquivo para MinIO
      if ($request->hasFile('arquivo')) {
        $arquivo = $request->file('arquivo');

        // Usar nome da especificação para gerar nome do arquivo
        $nomeArquivo = $this->generateEspecificacaoFileName(
          $request->nome,
          $arquivo->getClientOriginalExtension(),
        );

        // Verificar se já existe arquivo com mesmo nome e gerar nome único
        $nomeArquivo = $this->getUniqueEspecificacaoFileName($nomeArquivo);

        // Salvar no bucket 'especificacoes' via Helper
        StorageHelper::especificacoes()->putFileAs('/', $arquivo, $nomeArquivo);
      }

      Especificacao::create([
        'nome' => $request->nome,
        'arquivo' => $nomeArquivo,
        'status' => true,
        'usuario_id' => auth()->user()->id,
      ]);

      return redirect()
        ->route('especificacoes.especificacoes_list')
        ->withSuccess('Especificação cadastrada com sucesso!');
    } catch (\Exception $e) {
      Log::error('Erro ao salvar especificação: ' . $e->getMessage());
      return back()
        ->withInput()
        ->withErrors([
          'Erro interno no servidor, informe o administrador do sistema!',
        ]);
    }
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    try {
      $especificacao = Especificacao::findOrFail($id);
      return view(
        'especificacoes.especificacoes_edit',
        compact('especificacao'),
      );
    } catch (\Exception $e) {
      Log::error(
        'Erro ao carregar especificação para edição: ' . $e->getMessage(),
      );
      return redirect()
        ->route('especificacoes.especificacoes_list')
        ->withErrors(['Especificação não encontrada.']);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateEspecificacaoRequest $request, $id)
  {
    try {
      $especificacao = Especificacao::findOrFail($id);
      $nomeArquivo = $especificacao->arquivo;
      $mensagens = [];

      // Upload do novo arquivo (se fornecido)
      if ($request->hasFile('arquivo')) {
        // Remover arquivo antigo do MinIO
        if (
          $especificacao->arquivo &&
          StorageHelper::especificacoes()->exists($especificacao->arquivo)
        ) {
          StorageHelper::especificacoes()->delete($especificacao->arquivo);
        }

        // Usar nome da especificação para gerar nome do arquivo
        $nomeParaArquivo = $request->has('nome')
          ? $request->nome
          : $especificacao->nome;
        $nomeArquivo = $this->generateEspecificacaoFileName(
          $nomeParaArquivo,
          $request->file('arquivo')->getClientOriginalExtension(),
        );

        // Verificar se já existe arquivo com mesmo nome e gerar nome único
        $nomeArquivo = $this->getUniqueEspecificacaoFileName($nomeArquivo);

        // Salvar novo arquivo no MinIO
        StorageHelper::especificacoes()->putFileAs(
          '/',
          $request->file('arquivo'),
          $nomeArquivo,
        );
        $mensagens[] = 'Arquivo atualizado com sucesso!';
      }

      // Renomear arquivo existente se o nome foi alterado
      // Se o nome foi alterado e não houve upload de novo arquivo, renomear o arquivo existente
      if (
        $request->has('nome') &&
        $request->nome !== $especificacao->nome &&
        $especificacao->arquivo &&
        !$request->hasFile('arquivo')
      ) {
        try {
          // Obter extensão do arquivo atual
          $extensaoAtual = pathinfo(
            $especificacao->arquivo,
            PATHINFO_EXTENSION,
          );

          // Gerar novo nome baseado no novo nome da especificação
          $novoNome = $this->generateEspecificacaoFileName(
            $request->nome,
            $extensaoAtual,
          );
          $novoNome = $this->getUniqueEspecificacaoFileName($novoNome);

          // Se o nome for diferente do atual, renomear no MinIO
          if ($novoNome !== $especificacao->arquivo) {
            // Verificar se arquivo atual existe
            if (
              StorageHelper::especificacoes()->exists($especificacao->arquivo)
            ) {
              // Copiar arquivo com novo nome
              $conteudoArquivo = StorageHelper::especificacoes()->get(
                $especificacao->arquivo,
              );
              StorageHelper::especificacoes()->put($novoNome, $conteudoArquivo);

              // Excluir arquivo antigo
              StorageHelper::especificacoes()->delete($especificacao->arquivo);

              // Atualizar referência na especificação
              $nomeAntigoArquivo = $especificacao->arquivo;
              $nomeArquivo = $novoNome;

              Log::info('Arquivo da especificação renomeado', [
                'especificacao_id' => $especificacao->id,
                'nome_antigo' => $nomeAntigoArquivo,
                'nome_novo' => $novoNome,
                'usuario' => auth()->user()->name,
              ]);
            }
          }
        } catch (\Exception $e) {
          Log::error(
            'Erro ao renomear arquivo da especificação: ' . $e->getMessage(),
          );
        }
      }

      $especificacao->update([
        'nome' => $request->nome,
        'arquivo' => $nomeArquivo,
      ]);

      // Determinar mensagem de sucesso
      $mensagemFinal =
        count($mensagens) > 0
          ? 'Especificação atualizada com sucesso! ' . implode(' ', $mensagens)
          : 'Especificação atualizada com sucesso!';

      return redirect()
        ->route('especificacoes.especificacoes_list')
        ->withSuccess($mensagemFinal);
    } catch (\Exception $e) {
      Log::error('Erro ao atualizar especificação: ' . $e->getMessage());
      return back()
        ->withInput()
        ->withErrors([
          'Erro interno no servidor, informe o administrador do sistema!',
        ]);
    }
  }

  /**
   * Remove the specified resource from storage (soft delete).
   */
  public function destroy($id)
  {
    try {
      $especificacao = Especificacao::findOrFail($id);

      // Soft delete - apenas marca como inativo
      $especificacao->update(['status' => false]);

      return redirect()
        ->route('especificacoes.especificacoes_list')
        ->withSuccess('Especificação excluída com sucesso!');
    } catch (\Exception $e) {
      Log::error('Erro ao excluir especificação: ' . $e->getMessage());
      return back()->withErrors([
        'Erro ao excluir especificação: ' . $e->getMessage(),
      ]);
    }
  }

  /**
   * Download do arquivo PDF do MinIO
   */
  public function download($id)
  {
    try {
      $especificacao = Especificacao::findOrFail($id);

      if (!$especificacao->arquivo) {
        return back()->withErrors(['Arquivo não encontrado.']);
      }

      // BUSCAR NO BUCKET 'especificacoes
      if (!StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
        return back()->withErrors(['Arquivo não encontrado no servidor.']);
      }

      // Buscar arquivo do MinIO
      $conteudo = StorageHelper::especificacoes()->get($especificacao->arquivo);

      // Gerar nome para download
      $nomeDownload = $this->sanitize_filename($especificacao->nome) . '.pdf';

      return response($conteudo, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $nomeDownload . '"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
      ]);
    } catch (\Exception $e) {
      Log::error(
        'Erro ao fazer download da especificação: ' . $e->getMessage(),
      );
      return back()->withErrors(['Erro ao fazer download do arquivo.']);
    }
  }

  /**
   * Visualizar PDF no navegador
   */
  public function view($id)
  {
    try {
      $especificacao = Especificacao::findOrFail($id);

      if (!$especificacao->arquivo) {
        abort(404, 'Arquivo não encontrado');
      }

      // BUSCAR NO BUCKET 'especificacoes'
      if (!StorageHelper::especificacoes()->exists($especificacao->arquivo)) {
        abort(404, 'Arquivo não encontrado no servidor');
      }

      // Buscar arquivo do MinIO
      $conteudo = StorageHelper::especificacoes()->get($especificacao->arquivo);

      return response($conteudo, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' =>
          'inline; filename="' . $especificacao->nome . '.pdf"',
        'Cache-Control' => 'public, max-age=3600',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
      ]);
    } catch (\Exception $e) {
      Log::error('Erro ao visualizar especificação: ' . $e->getMessage());
      abort(404, 'Arquivo não encontrado');
    }
  }

  /**
   * Função para sanitizar nome de arquivo
   */
  private function sanitize_filename($filename)
  {
    // Remove caracteres especiais e substitui espaços por underscores
    $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    return trim($filename, '_');
  }
}
