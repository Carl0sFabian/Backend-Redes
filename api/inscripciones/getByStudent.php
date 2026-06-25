<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once '../../config/database.php';
include_once '../../models/Inscripcion.php';
include_once '../../controllers/InscripcionController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new InscripcionController($db);

$id_estudiante = isset($_GET['id_estudiante']) ? $_GET['id_estudiante'] : die(json_encode(array("message" => "Falta el parámetro id_estudiante.")));

echo $controller->getByStudentId($id_estudiante);
?>
