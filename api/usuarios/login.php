<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    $username = htmlspecialchars(strip_tags($data->username));
    $password = $data->password;

    $query = "SELECT id, codigo, nombre_completo, correo, password, tipo FROM usuarios WHERE correo = ? OR codigo = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $row['password'])) {
            http_response_code(200);
            unset($row['password']);
            echo json_encode(array(
                "success" => true,
                "message" => "Inicio de sesión exitoso.",
                "user" => $row
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Contraseña incorrecta."));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Usuario no registrado."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos. Se requiere usuario y contraseña."));
}
?>
