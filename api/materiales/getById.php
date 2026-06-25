<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

include_once '../../config/database.php';
include_once '../../models/Material.php';
include_once '../../controllers/MaterialController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new MaterialController($db);

$id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Falta el parámetro ID.")));

echo $controller->getMaterialById($id);
?>