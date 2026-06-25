<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';
include_once '../../models/Curso.php';
include_once '../../controllers/CursoController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new CursoController($db);

echo $controller->getCursos();
?>