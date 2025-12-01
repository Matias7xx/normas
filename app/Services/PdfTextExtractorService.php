<?php

namespace App\Services;

use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageHelper;
use Exception;

class PdfTextExtractorService
{
  private const MIN_WORDS_FOR_NATIVE = 35;
  private const MAX_OCR_PAGES = 50;

  /**
   * Extrai texto de um PDF (nativo primeiro → OCR só se necessário)
   */
  public function extractText(string $pdfPath): ?string
  {
    $tempPath = null;

    try {
      $tempPath = $this->downloadToTemp($pdfPath);

      if (!$tempPath || !file_exists($tempPath)) {
        Log::error("Falha ao baixar PDF do MinIO: {$pdfPath}");
        return null;
      }

      $texto = $this->extractNativeText($tempPath);

      if (!$this->isNativeTextSufficient($texto)) {
        Log::info("Texto nativo insuficiente ({$pdfPath}), iniciando OCR...");
        $texto = $this->extractWithOcr($tempPath);
      } else {
        Log::info("Texto nativo extraído com sucesso ({$pdfPath})");
      }

      return $this->cleanText($texto);
    } catch (Exception $e) {
      Log::error(
        "Erro inesperado ao extrair texto do PDF {$pdfPath}: " .
          $e->getMessage(),
        [
          'exception' => $e,
        ],
      );
      return null;
    } finally {
      if ($tempPath && file_exists($tempPath)) {
        @unlink($tempPath);
      }
    }
  }

  private function downloadToTemp(string $pdfPath): ?string
  {
    try {
      $content = StorageHelper::boletins()->get($pdfPath);

      if ($content === false || $content === null) {
        return null;
      }

      $tempPath = tempnam(sys_get_temp_dir(), 'pdf_extract_') . '.pdf';
      file_put_contents($tempPath, $content);

      return $tempPath;
    } catch (Exception $e) {
      Log::error('Erro ao baixar PDF do storage: ' . $e->getMessage());
      return null;
    }
  }

  private function extractNativeText(string $pdfPath): string
  {
    try {
      $text = Pdf::getText($pdfPath);

      if (!empty($text) && !mb_check_encoding($text, 'UTF-8')) {
        $detected = mb_detect_encoding(
          $text,
          ['UTF-8', 'ISO-8859-1', 'Windows-1252'],
          true,
        );
        $from = $detected ?: 'ISO-8859-1';

        $text = mb_convert_encoding($text, 'UTF-8', $from);
      }

      return $text ?: '';
    } catch (Exception $e) {
      Log::warning('Falha na extração nativa (pdftotext): ' . $e->getMessage());
      return '';
    }
  }

  private function isNativeTextSufficient(string $text): bool
  {
    $text = trim($text);
    if (strlen($text) < 100) {
      return false;
    }

    $wordCount = str_word_count($text);
    if ($wordCount < self::MIN_WORDS_FOR_NATIVE) {
      return false;
    }

    $fakeIndicators = [
      'created with',
      'evaluation copy',
      'do not copy',
      'pdf created by',
      'unregistered version',
    ];

    $lower = strtolower($text);
    foreach ($fakeIndicators as $indicator) {
      if (strpos($lower, $indicator) !== false && $wordCount < 100) {
        return false;
      }
    }

    return true;
  }

  private function extractWithOcr(string $pdfPath): string
  {
    if (!extension_loaded('imagick')) {
      Log::warning('Extensão Imagick não disponível');
      return '';
    }

    try {
      $fullText = '';

      // Lê o número de páginas primeiro
      $imagick = new \Imagick();
      $imagick->pingImage($pdfPath);
      $pageCount = $imagick->getNumberImages();
      $imagick->clear();

      for ($i = 0; $i < $pageCount; $i++) {
        $page = new \Imagick();
        $page->setResolution(300, 300);
        $page->readImage("{$pdfPath}[$i]");
        $page->setImageFormat('png');

        $tempImage = tempnam(sys_get_temp_dir(), 'ocr_') . '.png';
        $page->writeImage($tempImage);

        $output = shell_exec(
          "tesseract {$tempImage} stdout -l por --psm 6 2>/dev/null",
        );
        $fullText .= trim($output ?? '') . "\n\n";

        @unlink($tempImage);
        $page->clear();
      }

      return $fullText;
    } catch (\Exception $e) {
      Log::warning('Erro no OCR: ' . $e->getMessage());
      return '';
    }
  }

  private function cleanText(?string $text): ?string
  {
    if ($text === null || $text === '') {
      return null;
    }

    // Força UTF-8
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);

    // Normaliza espaços e quebras
    $text = preg_replace('/[ \t]+/u', ' ', $text);
    $text = preg_replace('/\R{3,}/u', "\n\n", $text);
    $text = trim($text);

    // Remove linhas completamente vazias
    $text = rtrim($text, "\n");

    return $text !== '' ? $text : null;
  }
}
