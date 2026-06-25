<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';
include_once '../../models/Asistencia.php';
include_once '../../controllers/AsistenciaController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new AsistenciaController($db);

echo $controller->getAsistencias();
?>