<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

class TranslationService {

    
    public function extractText($filePath, $extension) {
        $extension = strtolower($extension);
        if ($extension === 'txt') {
            return file_get_contents($filePath);
        } elseif ($extension === 'pdf') {
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                return $pdf->getText();
            } catch (Exception $e) {
                error_log("Error al extraer texto del PDF: " . $e->getMessage());
                return "";
            }
        }
        return "";
    }

    
    public function translateToSpanish($text) {
        if (empty(trim($text))) {
            return ['detected_language' => null, 'translated_text' => null];
        }

        
        $sampleText = mb_substr($text, 0, 300);
        $sampleData = $this->callTranslateApi($sampleText);

        if (!$sampleData) {
            return ['detected_language' => null, 'translated_text' => null];
        }

        $detectedLanguage = $sampleData['source_language'];

        
        if ($detectedLanguage !== 'en') {
            return ['detected_language' => $detectedLanguage, 'translated_text' => null];
        }

        
        $chunks = $this->splitTextIntoChunks($text, 350);
        $translatedText = "";

        foreach ($chunks as $chunk) {
            if (empty(trim($chunk))) continue;

            $result = $this->callTranslateApi($chunk);
            if ($result && !empty($result['translated'])) {
                $translatedText .= $result['translated'] . "\n";
            }
            
            
            usleep(200000);
        }

        return [
            'detected_language' => $detectedLanguage,
            'translated_text' => trim($translatedText)
        ];
    }

    
    private function callTranslateApi($text) {
        $url = "https://api.mymemory.translated.net/get?q=" . urlencode($text) . "&langpair=en|es";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            error_log("Error al consultar API de traducción MyMemory. HTTP Code: " . $httpCode);
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error de JSON en la respuesta de MyMemory");
            return null;
        }

        $translated = isset($data['responseData']['translatedText']) ? $data['responseData']['translatedText'] : "";
        
        $sourceLanguage = 'en'; 
        if (isset($data['matches']) && is_array($data['matches']) && count($data['matches']) > 0) {
            $firstMatch = $data['matches'][0];
            if (isset($firstMatch['source'])) {
                $sourceLanguage = strtolower(substr($firstMatch['source'], 0, 2));
            }
        }

        return [
            'translated' => $translated,
            'source_language' => $sourceLanguage
        ];
    }

    
    private function splitTextIntoChunks($text, $maxLength) {
        
        $text = preg_replace("/\r\n|\r|\n/", "\n", $text);
        
        
        $wrapped = wordwrap($text, $maxLength, "\n|SPLIT_CHUNK|\n");
        return explode("\n|SPLIT_CHUNK|\n", $wrapped);
    }

    
    public function generateTranslatedPdf($text, $title, $outputPath) {
        $pdf = new FPDF();
        $pdf->AddPage();
        
        
        $pdf->SetFont('Arial', 'B', 16);
        $titleText = "Documento Traducido: " . $title;
        
        $pdf->Cell(0, 10, $this->encodeForPdf($titleText), 0, 1, 'C');
        $pdf->Ln(10);

        
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 7, $this->encodeForPdf($text));
        
        
        $pdf->Output('F', $outputPath);
    }

    
    private function encodeForPdf($text) {
        if (function_exists('iconv')) {
            return iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
        } elseif (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        }
        return utf8_decode($text); 
    }
}
