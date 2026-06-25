<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

include_once '../../config/database.php';
include_once '../../models/Usuario.php';
include_once '../../controllers/UsuarioController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new UsuarioController($db);


$id = isset($_GET['id']) ? $_GET['id'] : die();

echo $controller->getUsuarioById($id);
?>