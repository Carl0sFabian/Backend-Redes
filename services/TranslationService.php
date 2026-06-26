<?php

class TranslationService {

    private $apiKey;
    private $apiUrl;

public function __construct() {
        $envKey = getenv('DEEPL_API_KEY');
        
        // Si por alguna razón no la lee (dependiendo de tu configuración de PHP), busca en $_ENV o $_SERVER
        if (!$envKey) {
            $envKey = isset($_ENV['DEEPL_API_KEY']) ? $_ENV['DEEPL_API_KEY'] : (isset($_SERVER['DEEPL_API_KEY']) ? $_SERVER['DEEPL_API_KEY'] : null);
        }

        if (!$envKey) {
            error_log("CRÍTICO: No se encontró la variable de entorno DEEPL_API_KEY.");
        }

        $this->apiKey = $envKey; 
        $this->apiUrl = "https://api-free.deepl.com/v2"; // Se mantiene igual para cuentas Free
    }

    /**
     * Traduce un documento completo (PDF o TXT) usando la API de DeepL
     * @return string|null Ruta del archivo traducido en el servidor temporal
     */
    public function translateDocument($filePath, $originalName, $extension) {
        try {
            // 1. Subir el documento a DeepL
            $uploadData = $this->uploadDocument($filePath, $originalName);
            if (!$uploadData) return null;

            $documentId = $uploadData['document_id'];
            $documentKey = $uploadData['document_key'];

            // 2. Esperar a que se procese la traducción (Polling)
            $status = $this->waitForTranslation($documentId, $documentKey);
            if ($status !== 'done') {
                error_log("La traducción en DeepL falló o fue cancelada. Estado: " . $status);
                return null;
            }

            // 3. Descargar el archivo traducido
            $tempFileName = pathinfo($originalName, PATHINFO_FILENAME) . '_es.' . $extension;
            $outputPath = sys_get_temp_dir() . '/' . $tempFileName;

            if ($this->downloadDocument($documentId, $documentKey, $outputPath)) {
                return $outputPath; // Retorna la ruta del archivo listo
            }

            return null;
        } catch (Exception $e) {
            error_log("Excepción en TranslationService: " . $e->getMessage());
            return null;
        }
    }

    private function uploadDocument($filePath, $originalName) {
        $url = $this->apiUrl . "/document";
        
        // Preparar el archivo de forma segura para cURL
        $cfile = new CURLFile($filePath, mime_content_type($filePath), $originalName);

        $postData = [
            'file' => $cfile,
            'target_lang' => 'ES',
            'source_lang' => 'EN' // Forzamos que traduzca de Inglés a Español
        ];

        $headers = [
            "Authorization: DeepL-Auth-Key " . $this->apiKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            error_log("DeepL Upload Error. HTTP: " . $httpCode . " Resp: " . $response);
            return null;
        }

        return json_decode($response, true);
    }

    private function waitForTranslation($documentId, $documentKey) {
        $url = $this->apiUrl . "/document/" . $documentId;
        $headers = [
            "Authorization: DeepL-Auth-Key " . $this->apiKey
        ];

        $postData = [
            'document_key' => $documentKey
        ];

        while (true) {
            $ch = curl_init();
            // Pasamos parámetros por POST para proteger la document_key
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($response, true);
            $status = isset($data['status']) ? $data['status'] : 'error';

            // Estados posibles: queued, translating, done, error
            if ($status === 'done' || $status === 'error') {
                return $status;
            }

            // Esperar 2 segundos antes de volver a preguntar (evita saturar la API)
            sleep(2);
        }
    }

    private function downloadDocument($documentId, $documentKey, $outputPath) {
        $url = $this->apiUrl . "/document/" . $documentId . "/result";
        $headers = [
            "Authorization: DeepL-Auth-Key " . $this->apiKey
        ];

        $postData = [
            'document_key' => $documentKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            error_log("Error al descargar documento traducido de DeepL. HTTP: " . $httpCode);
            return false;
        }

        // Guardar el binario recibido (sea PDF o TXT) en la ruta de salida
        return file_put_contents($outputPath, $response) !== false;
    }
}