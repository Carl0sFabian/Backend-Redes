<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();

$client->setAuthConfig(__DIR__ . '/config/client_secret.json');


$client->setRedirectUri('https://backend-redes-kbr6.onrender.com/get_token.php');

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
        $token_json = json_encode($token, JSON_PRETTY_PRINT);
        file_put_contents(__DIR__ . '/config/token.json', $token_json);
        
        echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>";
        echo "<h3 style='color:#2e7d32; margin-top: 0;'>¡Éxito! Token generado correctamente</h3>";
        echo "<p style='color:#555;'>Tu backend en Render ha guardado el token de forma temporal, pero para que sea <strong>permanente</strong> y no se borre al reiniciar el servidor, debes guardarlo en tus <strong>Secret Files</strong> de Render.</p>";
        
        // Botón de descarga
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='data:application/json;charset=utf-8," . rawurlencode($token_json) . "' download='token.json' style='display: inline-block; padding: 10px 20px; background: #1a73e8; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;'>Descargar token.json</a>";
        echo "</div>";
        
        // Cuadro de texto para copiar
        echo "<p style='margin-bottom: 5px; font-weight: bold; color: #333;'>O copia el contenido de abajo:</p>";
        echo "<textarea readonly style='width: 100%; height: 180px; font-family: monospace; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: #f9f9f9; font-size: 12px; resize: none;' onclick='this.select()'>" . htmlspecialchars($token_json) . "</textarea>";
        echo "<p style='font-size: 12px; color: #666; margin-top: 5px;'>Haz clic dentro del cuadro para seleccionar todo el texto y copiarlo.</p>";
        
        echo "<hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>";
        echo "<p style='font-size: 13px; color: #666;'>Una vez copiado o descargado, ve a Render -> Environment -> Secret Files y actualiza tu archivo <code>token.json</code> con este contenido.</p>";
        echo "</div>";
    }
}
?>
