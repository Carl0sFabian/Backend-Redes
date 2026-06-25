<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once '../../config/database.php';
include_once '../../models/Asistencia.php';

$database = new Database();
$db = $database->getConnection();
$controller = new AsistenciaController($db);

$id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Falta el parámetro id.")));

echo $controller->getAsistenciasByUserId($id);
?>
