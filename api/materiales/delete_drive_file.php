<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once '../../services/GoogleDriveService.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->drive_id)) {
    try {
        $driveService = new GoogleDriveService();
        if ($driveService->deleteFile($data->drive_id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Archivo eliminado de Google Drive exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo eliminar el archivo de Google Drive."));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Debe proporcionar el ID del archivo de Drive a eliminar."));
}
?>
