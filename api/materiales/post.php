<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/Material.php';
include_once '../../controllers/MaterialController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new MaterialController($db);



echo $controller->createMaterial($_POST, $_FILES);
?>