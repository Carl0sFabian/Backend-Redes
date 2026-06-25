<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once '../../config/database.php';
include_once '../../models/Asistencia.php';
include_once '../../controllers/AsistenciaController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new AsistenciaController($db);

$id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : die(json_encode(array("message" => "Falta el parámetro id_usuario.")));

echo $controller->getAsistenciasByUserId($id_usuario);
?>
