<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

include_once '../../config/database.php';
include_once '../../models/Curso.php';
include_once '../../controllers/CursoController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new CursoController($db);

$id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Falta el parámetro ID.")));

echo $controller->getCursoById($id);
?>