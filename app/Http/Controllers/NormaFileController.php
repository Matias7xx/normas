<?php

namespace App\Http\Controllers;

use App\Models\Norma;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageHelper;

class NormaFileController extends Controller
{
    /**
     * Visualizar PDF
     */
    public function view($id)
    {
        if (!Auth::check()) {
            abort(403, 'Acesso negado');
        }

        $norma = Norma::where('status', true)->findOrFail($id);
        
        if (!$norma->anexo) {
            abort(404, 'Arquivo não encontrado');
        }

        // HELPER PARA BUCKET normas
        if (!StorageHelper::normas()->exists($norma->anexo)) {
            abort(404, 'Arquivo não encontrado no servidor');
        }

        try {
            // ARQUIVO DO BUCKET normas
            $conteudo = StorageHelper::normas()->get($norma->anexo);
            
            return response($conteudo, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $norma->anexo . '"',
                'Cache-Control' => 'public, max-age=3600', // Cache por 1 hora
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Erro ao buscar arquivo da norma {$id}: " . $e->getMessage());
            abort(500, 'Erro ao carregar arquivo');
        }
    }

    /**
     * Download PDF
     */
    public function download($id)
    {
        if (!Auth::check()) {
            abort(403, 'Acesso negado');
        }

        $norma = Norma::where('status', true)->findOrFail($id);
        
        if (!$norma->anexo) {
            abort(404, 'Arquivo não encontrado');
        }

        // HELPER PARA BUCKET
        if (!StorageHelper::normas()->exists($norma->anexo)) {
            abort(404, 'Arquivo não encontrado no servidor');
        }

        try {
            // Gerar nome para download
            $fileName = $this->generateFileName($norma);
            
            //  ARQUIVO DO BUCKET
            $conteudo = StorageHelper::normas()->get($norma->anexo);
            
            return response($conteudo, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Erro ao baixar arquivo da norma {$id}: " . $e->getMessage());
            abort(500, 'Erro ao baixar arquivo');
        }
    }

    /**
     * Gera nome de arquivo para download
     */
    private function generateFileName($norma)
    {
        if ($norma->descricao) {
            // Limpar caracteres especiais da descrição
            $nome = preg_replace('/[^A-Za-z0-9\-_\s]/', '', $norma->descricao);
            $nome = preg_replace('/\s+/', '_', trim($nome));
            $nome = substr($nome, 0, 100); // Limitar tamanho
            return $nome . '.pdf';
        }
        
        return 'norma_' . $norma->id . '.pdf';
    }

    /**
     * obter info do arquivo
     */
    public function info($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $norma = Norma::where('status', true)->findOrFail($id);
        
        if (!$norma->anexo) {
            return response()->json(['error' => 'File not found'], 404);
        }

        try {
            // USAR HELPER PARA BUCKET 'normas'
            $exists = StorageHelper::normas()->exists($norma->anexo);
            $size = $exists ? StorageHelper::normas()->size($norma->anexo) : null;
            
            return response()->json([
                'id' => $norma->id,
                'filename' => $norma->anexo,
                'exists' => $exists,
                'size' => $size,
                'size_human' => $size ? $this->formatBytes($size) : null,
                'view_url' => route('normas.view', $norma->id),
                'download_url' => route('normas.download', $norma->id)
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error getting file info'], 500);
        }
    }

    /**
     * Formatar tamanho do arquivo
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}