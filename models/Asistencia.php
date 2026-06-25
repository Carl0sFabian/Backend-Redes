<?php
class Asistencia {
    private $conn;
    private $table_name = "asistencia_rfid";

    public $id;
    public $id_usuario;
    public $fecha_hora;
    public $sede;
    public $dispositivo_rfid;
    public $estado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function get() {
        $query = "SELECT a.id, a.id_usuario, a.fecha_hora, a.sede, a.dispositivo_rfid, a.estado, u.nombre_completo as usuario_nombre
                  FROM " . $this->table_name . " a
                  LEFT JOIN usuarios u ON a.id_usuario = u.id";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = "SELECT a.id, a.id_usuario, a.fecha_hora, a.sede, a.dispositivo_rfid, a.estado,
                         u.nombre_completo as usuario_nombre
                  FROM " . $this->table_name . " a
                  LEFT JOIN usuarios u ON a.id_usuario = u.id
                  WHERE a.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function getByUserId() {
        $query = "SELECT a.id, a.id_usuario, a.fecha_hora, a.sede, a.dispositivo_rfid, a.estado
                  FROM " . $this->table_name . " a
                  WHERE a.id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_usuario", $this->id_usuario);
        $stmt->execute();
        return $stmt;
    }

    public function getBySede() {
        $query = "SELECT a.id, a.id_usuario, a.fecha_hora, a.sede, a.dispositivo_rfid, a.estado
                  FROM " . $this->table_name . " a
                  WHERE a.sede = :sede";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sede", $this->sede);
        $stmt->execute();
        return $stmt;
    }

    public function getByState() {
        $query = "SELECT a.id, a.id_usuario, a.fecha_hora, a.sede, a.dispositivo_rfid, a.estado
                  FROM " . $this->table_name . " a
                  WHERE a.estado = :estado";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->execute();
        return $stmt;
    }

    public function post() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_usuario, fecha_hora, sede, dispositivo_rfid, estado) 
                  VALUES (:id_usuario, :fecha_hora, :sede, :dispositivo_rfid, :estado)";

        $stmt = $this->conn->prepare($query);

        
        $this->id_usuario = htmlspecialchars(strip_tags($this->id_usuario));
        $this->fecha_hora = htmlspecialchars(strip_tags($this->fecha_hora));
        $this->sede = htmlspecialchars(strip_tags($this->sede));
        $this->dispositivo_rfid = htmlspecialchars(strip_tags($this->dispositivo_rfid));
        $this->estado = htmlspecialchars(strip_tags($this->estado));

        $stmt->bindParam(":id_usuario", $this->id_usuario);
        $stmt->bindParam(":fecha_hora", $this->fecha_hora);
        $stmt->bindParam(":sede", $this->sede);
        $stmt->bindParam(":dispositivo_rfid", $this->dispositivo_rfid);
        $stmt->bindParam(":estado", $this->estado);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
}
?>