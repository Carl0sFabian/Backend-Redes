<?php
class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;

        
        $env = [];
        $vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        foreach ($vars as $var) {
            $val = getenv($var);
            if ($val !== false) {
                $env[$var] = $val;
            } elseif (isset($_SERVER[$var])) {
                $env[$var] = $_SERVER[$var];
            } elseif (isset($_ENV[$var])) {
                $env[$var] = $_ENV[$var];
            }
        }

        $envFiles = ['/etc/secrets/.env', __DIR__ . '/../.env'];
        foreach ($envFiles as $file) {
            if (file_exists($file)) {
                $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if (is_array($lines)) {
                    foreach ($lines as $line) {
                        if (strpos(trim($line), '#') === 0) continue;
                        $parts = explode('=', $line, 2);
                        if (count($parts) === 2) {
                            $key = trim($parts[0]);
                            $value = trim($parts[1]);
                            $value = trim($value, '"\'');
                            $env[$key] = $value;
                        }
                    }
                }
                break;
            }
        }
        
        $host = $env['DB_HOST'] ?? '';
        $port = $env['DB_PORT'] ?? '';
        $db_name = $env['DB_NAME'] ?? '';
        $username = $env['DB_USER'] ?? '';
        $password = $env['DB_PASS'] ?? '';

        try {
            $caPath = __DIR__ . '/ca.pem';
            if (file_exists('/etc/secrets/ca.pem') && is_readable('/etc/secrets/ca.pem')) {
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