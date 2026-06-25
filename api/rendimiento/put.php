<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/Rendimiento.php';
include_once '../../controllers/RendimientoController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new RendimientoController($db);

$data = json_decode(file_get_contents("php://input"));
echo $controller->updateRendimiento($data);
?>