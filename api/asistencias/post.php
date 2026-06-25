<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once '../../config/database.php';
include_once '../../models/Asistencia.php';
include_once '../../controllers/AsistenciaController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new AsistenciaController($db);


$data = json_decode(file_get_contents("php://input"));

echo $controller->createAsistencia($data);
?>
