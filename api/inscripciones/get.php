<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';
include_once '../../models/Inscripcion.php';
include_once '../../controllers/InscripcionController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new InscripcionController($db);

echo $controller->getInscripciones();
?>