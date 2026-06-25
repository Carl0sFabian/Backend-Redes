<?php
class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;

        
        $envPath = __DIR__ . '/../.env';
        
        if (!file_exists($envPath)) {
            die("Error: No se encontró el archivo .env");
        }
        
        $env = parse_ini_file($envPath);

        
        $host = $env['DB_HOST'];
        $port = $env['DB_PORT'];
        $db_name = $env['DB_NAME'];
        $username = $env['DB_USER'];
        $password = $env['DB_PASS'];

        try {
            
            
            $options = array(
                1014 => __DIR__ . '/ca.pem',
            );

            $dsn = "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $db_name;
            $this->conn = new PDO($dsn, $username, $password, $options);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            
            http_response_code(500);
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array(
                "error" => "db_connection_error",
                "message" => "No se pudo conectar a la base de datos. Detalle técnico: " . $exception->getMessage()
            ));
            exit();
        }

        return $this->conn;
    }
}
?>