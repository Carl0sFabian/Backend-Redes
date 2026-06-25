<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once '../../config/database.php';
include_once '../../models/Asistencia.php';
include_once '../../controllers/AsistenciaController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new AsistenciaController($db);

$sede = isset($_GET['sede']) ? $_GET['sede'] : die(json_encode(array("message" => "Falta el parámetro sede.")));

echo $controller->getAsistenciasByUserId($sede);
?>
