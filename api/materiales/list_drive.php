<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../services/GoogleDriveService.php';

try {
    $driveService = new GoogleDriveService();
    $files = $driveService->listFiles();
    
    $fileList = [];
    foreach ($files as $file) {
        $fileList[] = [
            'id' => $file->getId(),
            'name' => $file->getName(),
            'mimeType' => $file->getMimeType(),
            'webViewLink' => $file->getWebViewLink(),
            'size' => $file->getSize(),
            'createdTime' => $file->getCreatedTime()
        ];
    }
    
    http_response_code(200);
    echo json_encode($fileList);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error al listar archivos de Google Drive: " . $e->getMessage()));
}
?>
