<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();

$client->setAuthConfig(__DIR__ . '/config/client_secret.json');


$client->setRedirectUri('http://localhost:8012/edubridge-backend/get_token.php');

$client->addScope(Google_Service_Drive::DRIVE);
$client->setAccessType('offline'); 
$client->setPrompt('select_account consent'); 


if (!isset($_GET['code'])) {
    $auth_url = $client->createAuthUrl();
    echo "<h2>Autorización de Google Drive para Edubridge</h2>";
    echo "<a href='" . filter_var($auth_url, FILTER_SANITIZE_URL) . "' style='padding: 10px 20px; background: #4285F4; color: white; text-decoration: none; border-radius: 5px;'>Haz clic aquí para autorizar tu cuenta</a>";
} else {
    
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if(array_key_exists('error', $token)) {
        echo "Error al obtener el token: " . var_export($token, true);
    } else {
        
        file_put_contents(__DIR__ . '/config/token.json', json_encode($token));
        echo "<h3 style='color:green;'>¡Éxito! Token guardado en config/token.json</h3>";
        echo "<p>Tu backend ahora tiene acceso permanente. Ya puedes cerrar esta ventana y volver a intentar la prueba.</p>";
    }
}
?>