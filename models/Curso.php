<?php
class Curso {
    private $conn;
    private $table_name = "cursos";

    public $id;
    public $nombre;
    public $categoria;
    public $descripcion;
    public $temas;
    public $id_docente;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function get() {
        $query = "SELECT c.id, c.nombre, c.categoria, c.descripcion, c.temas, c.id_docente, u.nombre_completo as docente_nombre
                  FROM " . $this->table_name . " c
                  LEFT JOIN usuarios u ON c.id_docente = u.id";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = "SELECT c.id, c.nombre, c.categoria, c.descripcion, c.temas, c.id_docente, u.nombre_completo as docente_nombre
                  FROM " . $this->table_name . " c
                  LEFT JOIN usuarios u ON c.id_docente = u.id
                  WHERE c.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function post() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, categoria, descripcion, temas, id_docente) 
                  VALUES (:nombre, :categoria, :descripcion, :temas, :id_docente)";

        $stmt = $this->conn->prepare($query);

        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->temas = htmlspecialchars(strip_tags($this->temas));
        $this->id_docente = htmlspecialchars(strip_tags($this->id_docente));

        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":temas", $this->temas);
        $stmt->bindParam(":id_docente", $this->id_docente);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
}
?>