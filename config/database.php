<?php
class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;

        
        $envPath = __DIR__ . '/../.env';
        $etcSecretsEnv = '/etc/secrets/.env';
        
        if (file_exists($etcSecretsEnv)) {
            $env = parse_ini_file($etcSecretsEnv);
        } elseif (file_exists($envPath)) {
            $env = parse_ini_file($envPath);
        } else {
            $env = [
                'DB_HOST' => getenv('DB_HOST'),
                'DB_PORT' => getenv('DB_PORT'),
                'DB_NAME' => getenv('DB_NAME'),
                'DB_USER' => getenv('DB_USER'),
                'DB_PASS' => getenv('DB_PASS'),
            ];
        }
        
        $host = $env['DB_HOST'] ?? '';
        $port = $env['DB_PORT'] ?? '';
        $db_name = $env['DB_NAME'] ?? '';
        $username = $env['DB_USER'] ?? '';
        $password = $env['DB_PASS'] ?? '';

        try {
            $caPath = __DIR__ . '/ca.pem';
            if (file_exists('/etc/secrets/ca.pem')) {
                $caPath = '/etc/secrets/ca.pem';
            }
            $options = array(
                PDO::MYSQL_ATTR_SSL_CA => $caPath,
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