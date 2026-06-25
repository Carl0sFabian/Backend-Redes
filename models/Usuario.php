<?php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $codigo;
    public $nombre_completo;
    public $correo;
    public $password;
    public $tipo;
    public $fecha_creacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function get() {
        $query = "SELECT id, codigo, nombre_completo, correo, tipo, fecha_creacion FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function post() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (codigo, nombre_completo, correo, tipo, password) 
                  VALUES (:codigo, :nombre_completo, :correo, :tipo, :password)";

        $stmt = $this->conn->prepare($query);

        
        $this->codigo = htmlspecialchars(strip_tags($this->codigo));
        $this->nombre_completo = htmlspecialchars(strip_tags($this->nombre_completo));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        

        
        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":nombre_completo", $this->nombre_completo);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function getById() {
        $query = "SELECT id, codigo, nombre_completo, correo, tipo, fecha_creacion 
                  FROM " . $this->table_name . " 
                  WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        return $stmt;
    }

    public function put() {
        
        $query = "UPDATE " . $this->table_name . " 
                  SET codigo = :codigo, nombre_completo = :nombre_completo, correo = :correo, tipo = :tipo 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->codigo = htmlspecialchars(strip_tags($this->codigo));
        $this->nombre_completo = htmlspecialchars(strip_tags($this->nombre_completo));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":nombre_completo", $this->nombre_completo);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

?>