<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once '../../config/database.php';
include_once '../../models/Rendimiento.php';
include_once '../../controllers/RendimientoController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new RendimientoController($db);

$id_curso = isset($_GET['id_curso']) ? $_GET['id_curso'] : die(json_encode(array("message" => "Falta el parámetro id_curso.")));

echo $controller->getByCourseId($id_curso);
?>
